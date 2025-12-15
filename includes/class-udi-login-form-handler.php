<?php
/**
 * Processes login/registration/lost password forms.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Form_Handler {

	/**
	 * Plugin reference.
	 *
	 * @var UDI_Login_Plugin
	 */
	protected $plugin;

	/**
	 * Runtime notices to show in template.
	 *
	 * @var array
	 */
	protected $notices = array();

	/**
	 * Constructor.
	 *
	 * @param UDI_Login_Plugin $plugin Plugin object.
	 */
	public function __construct( UDI_Login_Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'init', array( $this, 'handle_request' ) );
	}

	/**
	 * Expose notices to renderer.
	 *
	 * @return array
	 */
	public function get_notices() {
		return $this->notices;
	}

	/**
	 * Handle POST submissions.
	 *
	 * @return void
	 */
	public function handle_request() {
		if ( empty( $_POST['udi_form_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$action = sanitize_key( wp_unslash( $_POST['udi_form_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		switch ( $action ) {
			case 'login':
				$this->process_login();
				break;
			case 'register':
				$this->process_register();
				break;
			case 'lostpassword':
				$this->process_lost_password();
				break;
			case 'resetpass':
				$this->process_reset_password();
				break;
		}
	}

	/**
	 * Process login request.
	 *
	 * @return void
	 */
	protected function process_login() {
		if ( ! isset( $_REQUEST['udi_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['udi_nonce'] ), 'udi_login_action' ) ) {
			$this->add_notice( 'error', __( 'Sessão expirada. Por favor, recarregue a página e tente novamente.', 'udi-custom-login' ) );
			return;
		}

		if ( ! $this->validate_security() ) {
			return;
		}

		$username = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';
		$password = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password should not be modified.
		$remember = ! empty( $_POST['rememberme'] );

		if ( empty( $username ) || empty( $password ) ) {
			$this->add_notice( 'error', __( 'Preencha usuário/e-mail e senha.', 'udi-custom-login' ) );
			return;
		}

		$creds = array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $creds, false );

		if ( is_wp_error( $user ) ) {
			// Log failed login
			udi_login_log_security_event(
				'login_failed',
				'Failed login attempt',
				array( 'username' => $username )
			);
			$this->add_notice( 'error', $user->get_error_message() );
			return;
		}

		wp_set_current_user( $user->ID );

		// Log successful login
		udi_login_log_security_event(
			'login_success',
			'User logged in successfully',
			array(
				'user_id'   => $user->ID,
				'username'  => $user->user_login,
			)
		);

		$redirect = $this->determine_redirect( 'login' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Process registration.
	 *
	 * @return void
	 */
	protected function process_register() {
		if ( ! isset( $_REQUEST['udi_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['udi_nonce'] ), 'udi_register_action' ) ) {
			$this->add_notice( 'error', __( 'Sessão expirada. Por favor, recarregue a página e tente novamente.', 'udi-custom-login' ) );
			return;
		}

		if ( ! get_option( 'users_can_register' ) ) {
			$this->add_notice( 'error', __( 'Registro desativado.', 'udi-custom-login' ) );
			return;
		}

		if ( ! $this->validate_security() ) {
			return;
		}

		$email    = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
		$password = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password should not be modified.
		$first    = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			$this->add_notice( 'error', __( 'Informe um e-mail válido.', 'udi-custom-login' ) );
			return;
		}

		if ( email_exists( $email ) ) {
			if ( udi_login_get_option( 'generic_error_messages', false ) ) {
				$this->add_notice( 'error', __( '<strong>ERRO</strong>: Não foi possível concluir o registro.', 'udi-custom-login' ) );
			} else {
				$this->add_notice( 'error', __( 'Este e-mail já está cadastrado.', 'udi-custom-login' ) );
			}
			return;
		}

		// Validate password strength
		$strength_check = udi_login_validate_password_strength( $password );
		if ( ! $strength_check['valid'] ) {
			$this->add_notice( 'error', $strength_check['message'] );
			return;
		}

		// Fallback: minimum 6 characters if strength validation is disabled
		if ( empty( $password ) || strlen( $password ) < 6 ) {
			$this->add_notice( 'error', __( 'Defina uma senha com no mínimo 6 caracteres.', 'udi-custom-login' ) );
			return;
		}

		$username = sanitize_user( $email, true );

		if ( username_exists( $username ) ) {
			$username = sanitize_user( current_time( 'timestamp' ) . wp_rand( 10, 99 ) );
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			$this->add_notice( 'error', $user_id->get_error_message() );
			return;
		}

		if ( $first ) {
			update_user_meta( $user_id, 'first_name', $first );
		}

		wp_new_user_notification( $user_id, null, 'both' );

		// Log user registration
		udi_login_log_security_event(
			'user_registered',
			'New user registered',
			array(
				'user_id' => $user_id,
				'email'   => $email,
			)
		);

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		$redirect = $this->determine_redirect( 'register' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle lost password (request reset link).
	 *
	 * @return void
	 */
	protected function process_lost_password() {
		if ( ! isset( $_REQUEST['udi_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['udi_nonce'] ), 'udi_lostpassword_action' ) ) {
			$this->add_notice( 'error', __( 'Sessão expirada. Por favor, recarregue a página e tente novamente.', 'udi-custom-login' ) );
			return;
		}

		if ( ! $this->validate_security() ) {
			return;
		}

		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';

		if ( empty( $user_login ) ) {
			$this->add_notice( 'error', __( 'Informe o e-mail da conta.', 'udi-custom-login' ) );
			return;
		}

		$result = retrieve_password( $user_login );

		if ( true === $result ) {
			$this->add_notice( 'success', __( 'Enviamos um e-mail com o link para redefinir sua senha.', 'udi-custom-login' ) );
		} else {
			$message = __( 'Não foi possível enviar o e-mail. Verifique os dados e tente novamente.', 'udi-custom-login' );

			if ( is_wp_error( $result ) ) {
				$message = $result->get_error_message();
			}

			$this->add_notice( 'error', $message );
		}
	}

	/**
	 * Process reset password (final step).
	 *
	 * @return void
	 */
	protected function process_reset_password() {
		if ( ! isset( $_REQUEST['udi_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['udi_nonce'] ), 'udi_resetpass_action' ) ) {
			$this->add_notice( 'error', __( 'Sessão expirada. Por favor, recarregue a página e tente novamente.', 'udi-custom-login' ) );
			return;
		}

		if ( ! $this->validate_security() ) {
			return;
		}

		$pass1 = isset( $_POST['pass1'] ) ? $_POST['pass1'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password should not be modified.
		$pass2 = isset( $_POST['pass2'] ) ? $_POST['pass2'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password should not be modified.
		$key   = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';

		if ( empty( $key ) || empty( $login ) ) {
			$this->add_notice( 'error', __( 'Token inválido.', 'udi-custom-login' ) );
			return;
		}

		if ( $pass1 !== $pass2 ) {
			$this->add_notice( 'error', __( 'As senhas não conferem.', 'udi-custom-login' ) );
			return;
		}

		if ( strlen( $pass1 ) < 6 ) {
			$this->add_notice( 'error', __( 'Use ao menos 6 caracteres.', 'udi-custom-login' ) );
			return;
		}

		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			$this->add_notice( 'error', $user->get_error_message() );
			return;
		}

		reset_password( $user, $pass1 );

		$_GET['udi_action'] = 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->add_notice( 'success', __( 'Senha redefinida com sucesso. Faça login para continuar.', 'udi-custom-login' ) );
	}

	/**
	 * Determine redirect URL for a given context.
	 *
	 * @param string $context login|register|lostpassword|resetpass.
	 *
	 * @return string
	 */
	protected function determine_redirect( $context ) {
		$map = array(
			'login'    => 'redirect_after_login',
			'register' => 'redirect_after_register',
			'logout'   => 'redirect_after_logout',
		);

		$url = '';

		if ( 'login' === $context && ! empty( $_POST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$redirect = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$url = wp_validate_redirect( $redirect, '' );
		}

		if ( empty( $url ) && isset( $map[ $context ] ) ) {
			$url = esc_url_raw( udi_login_get_option( $map[ $context ], '' ) );
		}

		if ( empty( $url ) ) {
			if ( function_exists( 'wc_get_page_permalink' ) ) {
				$url = wc_get_page_permalink( 'myaccount' );
			}

			if ( empty( $url ) ) {
				$url = home_url( '/' );
			}
		}

		return apply_filters( 'udi_login_redirect', $url, $context );
	}

	/**
	 * Adds notice to stack.
	 *
	 * @param string $type success|error|info.
	 * @param string $message Message.
	 *
	 * @return void
	 */
	protected function add_notice( $type, $message ) {
		$this->notices[] = array(
			'type'    => $type,
			'message' => wp_kses_post( $message ),
		);
	}

	/**
	 * Validate security (Honeypot + reCAPTCHA).
	 *
	 * @return bool
	 */
	protected function validate_security() {
		// 1. Honeypot
		if ( isset( $this->plugin->security ) && method_exists( $this->plugin->security, 'validate_honeypot' ) ) {
			if ( ! $this->plugin->security->validate_honeypot() ) {
				// Silent fail or generic error.
				$this->add_notice( 'error', __( 'Erro de validação de segurança.', 'udi-custom-login' ) );
				return false;
			}
		}

		// 2. reCAPTCHA
		if ( ! udi_login_get_option( 'enable_recaptcha', false ) ) {
			return true;
		}

		$secret = udi_login_get_option( 'recaptcha_secret_key', '' );

		if ( ! $secret ) {
			return true;
		}

		$response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';

		if ( empty( $response ) ) {
			$this->add_notice( 'error', __( 'Valide o captcha antes de continuar.', 'udi-custom-login' ) );
			return false;
		}

		$request = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $secret,
					'response' => $response,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			)
		);

		if ( is_wp_error( $request ) ) {
			$this->add_notice( 'error', __( 'Falha na validação do captcha.', 'udi-custom-login' ) );

			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $request ), true );

		if ( empty( $body['success'] ) ) {
			$this->add_notice( 'error', __( 'Captcha inválido. Tente novamente.', 'udi-custom-login' ) );

			return false;
		}

		return true;
	}
}
