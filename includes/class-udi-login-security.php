<?php
/**
 * Security helpers (HTTPS, rate limit, logout redirects).
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Security {

	/**
	 * Plugin instance.
	 *
	 * @var UDI_Login_Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param UDI_Login_Plugin $plugin Plugin instance.
	 */
	public function __construct( UDI_Login_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'template_redirect', array( $this, 'force_https' ), 0 );
		add_action( 'login_init', array( $this, 'force_https' ), 0 );
		add_filter( 'authenticate', array( $this, 'maybe_block_login' ), 30, 3 );
		add_action( 'wp_login_failed', array( $this, 'record_failed_login' ), 10, 1 );
		add_action( 'wp_login', array( $this, 'clear_login_attempts' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'redirect_after_logout' ), 20 );
		
		// Generic error messages
		add_filter( 'login_errors', array( $this, 'obfuscate_login_errors' ) );
	}

	/**
	 * Ensure login related pages run over HTTPS.
	 *
	 * @return void
	 */
	public function force_https() {
		if ( is_ssl() ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			return;
		}

		if ( empty( $_SERVER['HTTP_HOST'] ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$site_scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );

		if ( 'https' !== $site_scheme && ! defined( 'FORCE_SSL_ADMIN' ) ) {
			return;
		}

		// Only enforce on login contexts.
		if ( ! $this->plugin->renderer->is_login_context() ) {
			return;
		}

		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		if ( $host && $uri ) {
			$redirect = 'https://' . $host . $uri;
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Block login attempts if IP is locked.
	 *
	 * @param WP_User|WP_Error|null $user     User or WP_Error instance.
	 * @param string                $username Username.
	 * @param string                $password Password.
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function maybe_block_login( $user, $username, $password ) {
		if ( ! $this->limit_enabled() ) {
			return $user;
		}

		$data = $this->get_lock_data();

		if ( $data && ! empty( $data['locked_until'] ) && time() < $data['locked_until'] ) {
			$remaining = (int) ceil( ( $data['locked_until'] - time() ) / 60 );

			// Log blocked attempt
			udi_login_log_security_event(
				'login_blocked',
				'Login attempt blocked due to too many failed attempts',
				array(
					'username'  => $username,
					'remaining' => $remaining,
				)
			);

			return new WP_Error(
				'udi_login_locked',
				sprintf(
					/* translators: %d minutes */
					esc_html__( 'Muitas tentativas falharam. Tente novamente em %d minutos.', 'udi-custom-login' ),
					max( 1, $remaining )
				)
			);
		}

		return $user;
	}

	/**
	 * Record failed login.
	 *
	 * @param string $username Username attempted.
	 *
	 * @return void
	 */
	public function record_failed_login( $username ) {
		if ( ! $this->limit_enabled() ) {
			return;
		}

		$data = $this->get_lock_data();

		if ( ! $data ) {
			$data = array(
				'attempts'    => 0,
				'locked_until'=> 0,
			);
		}

		$data['attempts']++;

		$max_attempts = max( 1, (int) udi_login_get_option( 'limit_login_attempts', 5 ) );
		$minutes      = max( 1, (int) udi_login_get_option( 'limit_login_lockout', 15 ) );

		if ( $data['attempts'] >= $max_attempts ) {
			$data['locked_until'] = time() + ( $minutes * 60 );
			$data['attempts']     = 0;

			// Log account lock
			udi_login_log_security_event(
				'account_locked',
				'IP locked due to excessive failed login attempts',
				array(
					'username'      => $username,
					'max_attempts'  => $max_attempts,
					'lockout_minutes' => $minutes,
				)
			);
		}

		set_transient( $this->get_lock_key(), $data, $minutes * 60 );
	}

	/**
	 * Clear attempts after successful login.
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user User object.
	 *
	 * @return void
	 */
	public function clear_login_attempts( $user_login, $user ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		delete_transient( $this->get_lock_key() );
	}

	/**
	 * Redirect on logout to configured page.
	 *
	 * @return void
	 */
	public function redirect_after_logout() {
		$url = esc_url_raw( udi_login_get_option( 'redirect_after_logout', '' ) );

		if ( $url ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Obfuscate login errors if enabled.
	 *
	 * @param string $error Error message.
	 * @return string
	 */
	public function obfuscate_login_errors( $error ) {
		if ( ! udi_login_get_option( 'generic_error_messages', false ) ) {
			return $error;
		}
		return __( '<strong>ERRO</strong>: Credenciais inválidas.', 'udi-custom-login' );
	}

	/**
	 * Validate honeypot field.
	 *
	 * @return bool True if valid (empty), False if bot detected (filled).
	 */
	public function validate_honeypot() {
		if ( ! udi_login_get_option( 'enable_honeypot', false ) ) {
			return true;
		}

		// The field name should be something tempting for bots but ignored by humans.
		// We'll use 'udi_login_website' or similar.
		if ( ! empty( $_POST['udi_hp_website'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether limit is enabled.
	 *
	 * @return bool
	 */
	protected function limit_enabled() {
		return (bool) udi_login_get_option( 'limit_login_enabled', true );
	}

	/**
	 * Current IP lock key.
	 *
	 * @return string
	 */
	protected function get_lock_key() {
		return 'udi_login_lock_' . md5( $this->get_ip_address() );
	}

	/**
	 * Retrieve existing data for current IP.
	 *
	 * @return array|null
	 */
	protected function get_lock_data() {
		$key = $this->get_lock_key();

		$data = get_transient( $key );

		return $data ? $data : null;
	}

	/**
	 * Get remote IP.
	 *
	 * @return string
	 */
	protected function get_ip_address() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Handle multiple IPs (comma separated)
		if ( strpos( $ip, ',' ) !== false ) {
			$ip_parts = explode( ',', $ip );
			$ip = trim( $ip_parts[0] );
		}

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}
}
