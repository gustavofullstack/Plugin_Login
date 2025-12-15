<?php
/**
 * Handles CSS/JS assets.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Assets {

	/**
	 * Plugin reference.
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

		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register scripts/styles.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'udi-custom-login',
			UDI_LOGIN_PLUGIN_URL . 'assets/css/login.css',
			array(),
			UDI_LOGIN_VERSION
		);

		wp_register_script(
			'udi-custom-login',
			UDI_LOGIN_PLUGIN_URL . 'assets/js/login.js',
			array( 'jquery' ),
			UDI_LOGIN_VERSION,
			true
		);

		wp_register_script(
			'udi-google-login',
			UDI_LOGIN_PLUGIN_URL . 'assets/js/google-login.js',
			array( 'jquery' ),
			UDI_LOGIN_VERSION,
			true
		);

		wp_register_style(
			'udi-google-signin',
			UDI_LOGIN_PLUGIN_URL . 'assets/css/google-signin.css',
			array(),
			UDI_LOGIN_VERSION
		);

		wp_register_style(
			'udi-my-account',
			UDI_LOGIN_PLUGIN_URL . 'assets/css/my-account.css',
			array(),
			UDI_LOGIN_VERSION
		);

		wp_register_style(
			'udi-admin',
			UDI_LOGIN_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			UDI_LOGIN_VERSION
		);

		wp_register_script(
			'udi-admin-js',
			UDI_LOGIN_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			UDI_LOGIN_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets on wp-login.php (used mainly for fallback or when default is forced).
	 *
	 * @return void
	 */
	public function enqueue_login_assets() {
		wp_enqueue_style( 'udi-custom-login' );
		wp_enqueue_script( 'udi-custom-login' );

		// Enqueue Google login script if enabled
		if ( udi_login_get_option( 'enable_google_signin', false ) ) {
			wp_enqueue_style( 'udi-google-signin' );
			wp_enqueue_script( 'udi-google-login' );
			wp_localize_script(
				'udi-google-login',
				'udiGoogleLogin',
				array(
					'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
					'errorMessage' => __( 'Erro ao fazer login com Google. Tente novamente.', 'udi-custom-login' ),
				)
			);
		}

		$this->localize_script();
		$this->enqueue_recaptcha();
	}

	/**
	 * Conditionally enqueue on the front-end.
	 *
	 * @return void
	 */
	public function maybe_enqueue_frontend_assets() {
		if ( ! $this->plugin->renderer->is_login_context() ) {
			return;
		}

		wp_enqueue_style( 'udi-custom-login' );
		wp_enqueue_script( 'udi-custom-login' );

		// Enqueue Google login script if enabled
		if ( udi_login_get_option( 'enable_google_signin', false ) ) {
			wp_enqueue_style( 'udi-google-signin' );
			wp_enqueue_script( 'udi-google-login' );
			wp_localize_script(
				'udi-google-login',
				'udiGoogleLogin',
				array(
					'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
					'errorMessage' => __( 'Erro ao fazer login com Google. Tente novamente.', 'udi-custom-login' ),
				)
			);
		}

		$this->localize_script();
		$this->enqueue_recaptcha();
	}

	/**
	 * Pass php data to JS.
	 *
	 * @return void
	 */
	protected function localize_script() {
		wp_localize_script(
			'udi-custom-login',
			'udiLogin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'currentView' => $this->plugin->renderer->get_current_view(),
				'settings'    => array(
					'headline'   => udi_login_get_option( 'headline', '' ),
					'subheadline'=> udi_login_get_option( 'subheadline', '' ),
				),
				'ajaxNonce'  => wp_create_nonce( 'udi_login_switch' ),
				'labelShowPassword' => __( 'Mostrar senha', 'udi-custom-login' ),
				'labelHidePassword' => __( 'Ocultar senha', 'udi-custom-login' ),
			)
		);
	}

	/**
	 * Admin assets (media buttons, minor styles).
	 *
	 * @param string $hook Current screen id.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_udi-login-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'udi-admin' );
		wp_enqueue_media();
		wp_enqueue_script( 'udi-admin-js' );
	}

	/**
	 * Maybe load reCAPTCHA script.
	 *
	 * @return void
	 */
	protected function enqueue_recaptcha() {
		if ( ! udi_login_get_option( 'enable_recaptcha', false ) ) {
			return;
		}

		if ( ! udi_login_get_option( 'recaptcha_site_key', '' ) ) {
			return;
		}

		wp_enqueue_script(
			'google-recaptcha',
			'https://www.google.com/recaptcha/api.js',
			array(),
			null,
			true
		);
	}
}
