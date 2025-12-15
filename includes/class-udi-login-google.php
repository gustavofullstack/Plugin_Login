<?php
/**
 * Google Sign-In Integration.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UDI_Login_Google
 *
 * Handles Google Sign-In integration with FedCM support.
 */
class UDI_Login_Google {

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	protected function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_google_script' ) );
		add_action( 'wp_ajax_nopriv_udi_google_login', array( $this, 'ajax_handle_google_login' ) );
		add_action( 'wp_ajax_udi_google_login', array( $this, 'ajax_handle_google_login' ) );
		add_action( 'udi_login_after_submit_button', array( $this, 'render_google_button' ) );
	}

	/**
	 * Check if Google Sign-In is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) udi_login_get_option( 'enable_google_signin', false );
	}

	/**
	 * Enqueue Google Identity Services script.
	 *
	 * @return void
	 */
	public function enqueue_google_script() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$client_id = udi_login_get_option( 'google_client_id', '' );
		if ( empty( $client_id ) ) {
			return;
		}

		// Only load on login pages
		if ( ! $this->plugin->renderer->is_login_context() && ! has_shortcode( get_the_content(), 'udi_custom_login' ) ) {
			return;
		}

		// Enqueue Google Identity Services library
		wp_enqueue_script(
			'google-identity-services',
			'https://accounts.google.com/gsi/client',
			array(),
			null,
			array(
				'strategy' => 'async',
				'in_footer' => false,
			)
		);
	}

	/**
	 * AJAX handler for Google login.
	 *
	 * @return void
	 */
	public function ajax_handle_google_login() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'udi_google_login' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sessão inválida.', 'udi-custom-login' ) ) );
		}

		// Get credential
		if ( ! isset( $_POST['credential'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Credencial não fornecida.', 'udi-custom-login' ) ) );
		}

		$credential = sanitize_text_field( wp_unslash( $_POST['credential'] ) );

		// Verify and decode token
		$payload = $this->verify_google_token( $credential );

		if ( ! $payload ) {
			udi_login_log_security_event(
				'google_login_failed',
				'Failed to verify Google token',
				array( 'reason' => 'invalid_token' )
			);
			wp_send_json_error( array( 'message' => __( 'Token do Google inválido.', 'udi-custom-login' ) ) );
		}

		// Validate email is verified
		if ( empty( $payload['email_verified'] ) || ! $payload['email_verified'] ) {
			udi_login_log_security_event(
				'google_login_failed',
				'Google email not verified',
				array( 'email' => $payload['email'] )
			);
			wp_send_json_error( array( 'message' => __( 'E-mail do Google não verificado.', 'udi-custom-login' ) ) );
		}

		// Try to authenticate or create user
		$result = $this->handle_google_user( $payload );

		if ( is_wp_error( $result ) ) {
			udi_login_log_security_event(
				'google_login_failed',
				$result->get_error_message(),
				array( 'email' => $payload['email'] )
			);
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Success - user is now logged in
		$redirect = $this->get_redirect_url();

		udi_login_log_security_event(
			'google_login_success',
			'User logged in via Google',
			array(
				'user_id'  => $result->ID,
				'email'    => $result->user_email,
				'is_new'   => get_user_meta( $result->ID, '_udi_google_new_user', true ),
			)
		);

		// Clean up temporary meta
		delete_user_meta( $result->ID, '_udi_google_new_user' );

		wp_send_json_success( array(
			'redirect' => $redirect,
			'message'  => __( 'Login realizado com sucesso!', 'udi-custom-login' ),
		) );
	}

	/**
	 * Verify Google ID token.
	 *
	 * @param string $token ID token from Google.
	 *
	 * @return array|false Payload if valid, false otherwise.
	 */
	protected function verify_google_token( $token ) {
		$client_id = udi_login_get_option( 'google_client_id', '' );

		if ( empty( $client_id ) ) {
			return false;
		}

		// Use Google API Client if available
		if ( class_exists( 'Google_Client' ) ) {
			try {
				$client = new Google_Client( array( 'client_id' => $client_id ) );
				$payload = $client->verifyIdToken( $token );

				if ( $payload ) {
					return $payload;
				}
			} catch ( Exception $e ) {
				// Fallback or log error
				udi_login_log_security_event(
					'google_login_failed',
					'Google API Client Exception: ' . $e->getMessage(),
					array( 'token_snippet' => substr( $token, 0, 10 ) . '...' )
				);
				return false;
			}
		}

		// Fallback to manual verification if library is missing (though it shouldn't be with Composer)
		// Decode JWT without verification (we'll verify signature via Google endpoint)
		$parts = explode( '.', $token );
		if ( count( $parts ) !== 3 ) {
			return false;
		}

		// Verify token with Google's tokeninfo endpoint (LEGACY FALLBACK)
		$response = wp_remote_get(
			'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode( $token ),
			array(
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || isset( $data['error'] ) ) {
			return false;
		}

		// Verify audience (client_id)
		if ( empty( $data['aud'] ) || $data['aud'] !== $client_id ) {
			return false;
		}

		// Verify issuer
		if ( empty( $data['iss'] ) || ! in_array( $data['iss'], array( 'accounts.google.com', 'https://accounts.google.com' ), true ) ) {
			return false;
		}

		// Verify expiration
		if ( empty( $data['exp'] ) || time() >= (int) $data['exp'] ) {
			return false;
		}

		return $data;
	}

	/**
	 * Handle Google user authentication or registration.
	 *
	 * @param array $payload Google token payload.
	 *
	 * @return WP_User|WP_Error User object on success, error on failure.
	 */
	protected function handle_google_user( $payload ) {
		$email      = sanitize_email( $payload['email'] );
		$google_id  = sanitize_text_field( $payload['sub'] );
		$name       = isset( $payload['name'] ) ? sanitize_text_field( $payload['name'] ) : '';
		$given_name = isset( $payload['given_name'] ) ? sanitize_text_field( $payload['given_name'] ) : '';
		$family_name = isset( $payload['family_name'] ) ? sanitize_text_field( $payload['family_name'] ) : '';
		$picture    = isset( $payload['picture'] ) ? esc_url_raw( $payload['picture'] ) : '';

		// Check if user exists by email
		$user = get_user_by( 'email', $email );

		if ( $user ) {
			// User exists - authenticate
			return $this->authenticate_google_user( $user, $google_id, $picture );
		}

		// Check if registration is allowed
		if ( ! get_option( 'users_can_register' ) ) {
			return new WP_Error( 'registration_disabled', __( 'Registro de novos usuários está desativado.', 'udi-custom-login' ) );
		}

		// Create new user
		return $this->create_user_from_google( $email, $google_id, $name, $given_name, $family_name, $picture );
	}

	/**
	 * Authenticate existing user via Google.
	 *
	 * @param WP_User $user       User object.
	 * @param string  $google_id  Google user ID.
	 * @param string  $picture    Profile picture URL.
	 *
	 * @return WP_User|WP_Error
	 */
	protected function authenticate_google_user( $user, $google_id, $picture ) {
		// Update Google ID if not set
		$stored_google_id = get_user_meta( $user->ID, 'udi_google_id', true );
		if ( empty( $stored_google_id ) ) {
			update_user_meta( $user->ID, 'udi_google_id', $google_id );
		}

		// Verify Google ID matches if already set
		if ( ! empty( $stored_google_id ) && $stored_google_id !== $google_id ) {
			return new WP_Error( 'google_id_mismatch', __( 'Esta Conta do Google está vinculada a outro usuário.', 'udi-custom-login' ) );
		}

		// Update profile picture
		if ( ! empty( $picture ) ) {
			update_user_meta( $user->ID, 'udi_google_picture', $picture );
		}

		// Update last login method
		update_user_meta( $user->ID, 'udi_last_login_method', 'google' );
		update_user_meta( $user->ID, 'udi_last_login_time', time() );

		// Set auth cookie
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );

		return $user;
	}

	/**
	 * Create new user from Google data.
	 *
	 * @param string $email       Email address.
	 * @param string $google_id   Google user ID.
	 * @param string $name        Full name.
	 * @param string $given_name  First name.
	 * @param string $family_name Last name.
	 * @param string $picture     Profile picture URL.
	 *
	 * @return WP_User|WP_Error
	 */
	protected function create_user_from_google( $email, $google_id, $name, $given_name, $family_name, $picture ) {
		// Generate username from email
		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// Make username unique
		$original_username = $username;
		$counter = 1;
		while ( username_exists( $username ) ) {
			$username = $original_username . $counter;
			$counter++;
		}

		// Generate random password (user won't need it)
		$password = wp_generate_password( 20, true, true );

		// Create user
		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Update user data
		$user_data = array(
			'ID'           => $user_id,
			'display_name' => ! empty( $name ) ? $name : $username,
		);

		if ( ! empty( $given_name ) ) {
			$user_data['first_name'] = $given_name;
		}

		if ( ! empty( $family_name ) ) {
			$user_data['last_name'] = $family_name;
		}

		wp_update_user( $user_data );

		// Store Google data
		update_user_meta( $user_id, 'udi_google_id', $google_id );
		update_user_meta( $user_id, 'udi_google_email', $email );
		update_user_meta( $user_id, 'udi_last_login_method', 'google' );
		update_user_meta( $user_id, '_udi_google_new_user', true );

		if ( ! empty( $picture ) ) {
			update_user_meta( $user_id, 'udi_google_picture', $picture );
		}

		// Send new user notification
		wp_new_user_notification( $user_id, null, 'both' );

		// Set auth cookie
		$user = get_user_by( 'id', $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		return $user;
	}

	/**
	 * Get redirect URL after Google login.
	 *
	 * @return string
	 */
	protected function get_redirect_url() {
		// Check for custom redirect
		if ( ! empty( $_POST['redirect_to'] ) ) {
			$redirect = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) );
			$redirect = wp_validate_redirect( $redirect, '' );
			if ( ! empty( $redirect ) ) {
				return $redirect;
			}
		}

		// Default redirect from settings
		$default = udi_login_get_option( 'redirect_after_login', '' );

		if ( ! empty( $default ) ) {
			return esc_url( $default );
		}

		// WooCommerce My Account
		if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
			return wc_get_account_endpoint_url( 'dashboard' );
		}

		// Default WordPress admin
		return admin_url();
	}

	/**
	 * Render Google Sign-In button and One Tap.
	 *
	 * @param string $context Context: 'login' or 'register'.
	 *
	 * @return void
	 */
	public function render_google_button( $context = 'login' ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$client_id = udi_login_get_option( 'google_client_id', '' );
		if ( empty( $client_id ) ) {
			return;
		}

		$enable_fedcm      = udi_login_get_option( 'google_enable_fedcm', true );
		$enable_onetap     = udi_login_get_option( 'google_enable_onetap', true );
		$enable_auto_select = udi_login_get_option( 'google_enable_auto_select', false );
		$button_type       = udi_login_get_option( 'google_button_type', 'standard' );
		$button_theme      = udi_login_get_option( 'google_button_theme', 'filled_blue' );
		$button_size       = udi_login_get_option( 'google_button_size', 'large' );
		$button_text       = udi_login_get_option( 'google_button_text', 'signin_with' );

		// Adjust button text for context
		if ( 'register' === $context && 'signin_with' === $button_text ) {
			$button_text = 'signup_with';
		}

		$nonce = wp_create_nonce( 'udi_google_login' );
		?>
		<div class="udi-google-signin">
			<div class="udi-divider">
				<span><?php esc_html_e( 'ou', 'udi-custom-login' ); ?></span>
			</div>

			<?php if ( $enable_onetap && 'login' === $context ) : ?>
				<div id="g_id_onload"
					 data-client_id="<?php echo esc_attr( $client_id ); ?>"
					 data-callback="udiHandleGoogleCredential"
					 data-context="<?php echo esc_attr( $context ); ?>"
					 data-ux_mode="popup"
					 data-auto_prompt="true"
					 <?php if ( $enable_auto_select ) : ?>
					 data-auto_select="true"
					 <?php endif; ?>
					 <?php if ( $enable_fedcm ) : ?>
					 data-use_fedcm_for_prompt="true"
					 <?php endif; ?>
					 data-itp_support="true"
					 data-cancel_on_tap_outside="false">
				</div>
			<?php endif; ?>

			<div class="g_id_signin"
				 data-type="<?php echo esc_attr( $button_type ); ?>"
				 data-shape="pill"
				 data-theme="<?php echo esc_attr( $button_theme ); ?>"
				 data-text="<?php echo esc_attr( $button_text ); ?>"
				 data-size="<?php echo esc_attr( $button_size ); ?>"
				 data-locale="pt-BR"
				 data-logo_alignment="left"
				 <?php if ( $enable_fedcm ) : ?>
				 data-use_fedcm_for_button="true"
				 <?php endif; ?>>
			</div>

			<input type="hidden" id="udi-google-nonce" value="<?php echo esc_attr( $nonce ); ?>">
		</div>
		<?php
	}
}
