<?php
/**
 * Handles template rendering for login UI.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Renderer {

	/**
	 * Plugin instance.
	 *
	 * @var UDI_Login_Plugin
	 */
	protected $plugin;

	/**
	 * Current view slug.
	 *
	 * @var string
	 */
	protected $current_view = 'login';

	/**
	 * Constructor.
	 *
	 * @param UDI_Login_Plugin $plugin Plugin reference.
	 */
	public function __construct( UDI_Login_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_filter( 'template_include', array( $this, 'maybe_override_template' ) );
		add_action( 'login_init', array( $this, 'maybe_redirect_login' ), 1 );
		add_action( 'template_redirect', array( $this, 'redirect_logged_in_users' ), 8 );
		add_action( 'wp_ajax_udi_login_switch_view', array( $this, 'ajax_switch_view' ) );
		add_action( 'wp_ajax_nopriv_udi_login_switch_view', array( $this, 'ajax_switch_view' ) );
	}

	/**
	 * Determine if assets should load.
	 *
	 * @return bool
	 */
	public function is_login_context() {
		if ( is_admin() && 'wp-login.php' !== $this->get_pagenow() ) {
			return false;
		}

		$page_id = (int) udi_login_get_option( 'login_page_id', 0 );

		if ( $page_id && is_page( $page_id ) ) {
			return true;
		}

		if ( is_singular() ) {
			global $post;
			if ( has_shortcode( $post->post_content, 'udi_custom_login' ) ) {
				return true;
			}
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
			return true;
		}

		if ( 'wp-login.php' === $this->get_pagenow() ) {
			return true;
		}

		return false;
	}

	/**
	 * Current action/view slug.
	 *
	 * @return string
	 */
	public function get_current_view() {
		return $this->current_view;
	}

	/**
	 * Render shortcode output.
	 *
	 * @param string $view Requested view.
	 * @param array  $args Additional data.
	 *
	 * @return string
	 */
	public function render( $view = 'login', $args = array() ) {
		$this->current_view = $this->sanitize_view( $view );
		ob_start();
		$template = UDI_LOGIN_PLUGIN_DIR . 'templates/layout.php';
		$form_notices = ( $this->plugin->forms ) ? $this->plugin->forms->get_notices() : array();
		$renderer     = $this;
		$data         = wp_parse_args(
			$args,
			array(
				'view'     => $this->current_view,
				'settings' => udi_login_get_settings(),
				'messages' => $form_notices,
			)
		);

		if ( file_exists( $template ) ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Return HTML for the requested form.
	 *
	 * @param string $view View slug.
	 *
	 * @return string
	 */
	public function get_form_html( $view ) {
		$this->current_view = $this->sanitize_view( $view );
		ob_start();

		switch ( $this->current_view ) {
			case 'register':
				include UDI_LOGIN_PLUGIN_DIR . 'templates/partials/form-register.php';
				break;
			case 'lostpassword':
				include UDI_LOGIN_PLUGIN_DIR . 'templates/partials/form-lostpassword.php';
				break;
			case 'resetpass':
				include UDI_LOGIN_PLUGIN_DIR . 'templates/partials/form-resetpass.php';
				break;
			case 'login':
			default:
				include UDI_LOGIN_PLUGIN_DIR . 'templates/partials/form-login.php';
				break;
		}

		return ob_get_clean();
	}

	/**
	 * Template override for login page slug.
	 *
	 * @param string $template Current template path.
	 *
	 * @return string
	 */
	public function maybe_override_template( $template ) {
		$page_id = (int) udi_login_get_option( 'login_page_id', 0 );

		if ( $page_id && is_page( $page_id ) ) {
			return UDI_LOGIN_PLUGIN_DIR . 'templates/page-login.php';
		}

		return $template;
	}

	/**
	 * Redirect wp-login.php to the custom page.
	 *
	 * @return void
	 */
	public function maybe_redirect_login() {
		if ( ! udi_login_get_option( 'enable_custom_login', true ) ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

		if ( in_array( $action, array( 'logout', 'postpass' ), true ) ) {
			return;
		}

		if ( isset( $_GET['default-login'] ) ) {
			return;
		}

		if ( 'wp-login.php' !== $this->get_pagenow() ) {
			return;
		}

		$page_id = (int) udi_login_get_option( 'login_page_id', 0 );

		if ( ! $page_id ) {
			return;
		}

		$url = get_permalink( $page_id );

		if ( ! $url ) {
			return;
		}

		$query_action = $action ? $action : 'login';

		$map = array(
			'rp'           => 'resetpass',
			'resetpass'    => 'resetpass',
			'lostpassword' => 'lostpassword',
			'register'     => 'register',
		);

		if ( isset( $map[ $query_action ] ) ) {
			$query_action = $map[ $query_action ];
		}

		$url = add_query_arg( 'udi_action', $query_action, $url );

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( wp_unslash( $_REQUEST['redirect_to'] ) ), $url );
		}

		if ( isset( $_REQUEST['key'] ) ) {
			$url = add_query_arg( 'key', rawurlencode( wp_unslash( $_REQUEST['key'] ) ), $url );
		}

		if ( isset( $_REQUEST['login'] ) ) {
			$url = add_query_arg( 'login', rawurlencode( wp_unslash( $_REQUEST['login'] ) ), $url );
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Determine current page slug.
	 *
	 * @return string
	 */
	protected function get_pagenow() {
		global $pagenow;

		return $pagenow;
	}

	/**
	 * Normalize requested view.
	 *
	 * @param string $view View.
	 *
	 * @return string
	 */
	protected function sanitize_view( $view ) {
		$allowed = array( 'login', 'register', 'lostpassword', 'resetpass' );

		if ( ! in_array( $view, $allowed, true ) ) {
			$view = 'login';
		}

		if ( 'register' === $view && ! get_option( 'users_can_register' ) ) {
			$view = 'login';
		}

		return $view;
	}

	/**
	 * AJAX endpoint for swapping views.
	 *
	 * @return void
	 */
	public function ajax_switch_view() {
		// Rate limiting: Allow max 20 requests per minute per IP
		$ip = $this->get_client_ip();
		$rate_key = 'udi_ajax_rate_' . md5( $ip );
		$rate_data = get_transient( $rate_key );
		
		if ( false === $rate_data ) {
			$rate_data = array( 'count' => 0, 'first_request' => time() );
		}
		
		$rate_data['count']++;
		
		// Reset counter if more than 60 seconds have passed
		if ( time() - $rate_data['first_request'] > 60 ) {
			$rate_data = array( 'count' => 1, 'first_request' => time() );
		}
		
		set_transient( $rate_key, $rate_data, 60 );
		
		// Block if too many requests
		if ( $rate_data['count'] > 20 ) {
			wp_send_json_error( array( 
				'message' => __( 'Excesso de requisições. Aguarde um momento.', 'udi-custom-login' ) 
			) );
		}
		
		// Verify nonce - note: ajaxNonce is set in class-udi-login-assets.php with action 'udi_login_switch'
		if ( ! check_ajax_referer( 'udi_login_switch', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Sessão inválida.', 'udi-custom-login' ) ) );
		}

		$view = isset( $_POST['view'] ) ? sanitize_key( wp_unslash( $_POST['view'] ) ) : 'login';
		$view = $this->sanitize_view( $view );

		wp_send_json_success(
			array(
				'view' => $view,
				'html' => $this->get_form_html( $view ),
			)
		);
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	protected function get_client_ip() {
		$ip = '';
		
		// Prioritize REMOTE_ADDR unless we explicitly trust proxies
		if ( defined( 'UDI_TRUST_PROXY' ) && UDI_TRUST_PROXY ) {
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			}
		}

		if ( empty( $ip ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		
		// Handle multiple IPs (comma separated)
		if ( strpos( $ip, ',' ) !== false ) {
			$ip_parts = explode( ',', $ip );
			$ip = trim( $ip_parts[0] );
		}
		
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}

	/**
	 * Redirect logged-in users attempting to access login forms.
	 *
	 * @return void
	 */
	public function redirect_logged_in_users() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass' ), true ) ) {
			return;
		}

		if ( ! $this->is_login_context() ) {
			return;
		}

		$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';

		if ( empty( $redirect ) && current_user_can( 'manage_options' ) ) {
			$redirect = admin_url();
		}

		if ( empty( $redirect ) && function_exists( 'wc_get_account_endpoint_url' ) ) {
			$redirect = wc_get_account_endpoint_url( 'dashboard' );
		}

		if ( empty( $redirect ) ) {
			$redirect = admin_url( 'profile.php' );
		}

		$redirect = apply_filters( 'udi_login_redirect_logged_in', $redirect );

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}
	/**
	 * Render honeypot field if enabled.
	 *
	 * @return void
	 */
	public function the_honeypot_field() {
		if ( ! udi_login_get_option( 'enable_honeypot', false ) ) {
			return;
		}
		?>
		<div style="position: absolute; left: -9999px; top: -9999px;">
			<label for="udi_hp_website"><?php esc_html_e( 'Website', 'udi-custom-login' ); ?></label>
			<input type="text" id="udi_hp_website" name="udi_hp_website" tabindex="-1" autocomplete="off" />
		</div>
		<?php
	}
}
