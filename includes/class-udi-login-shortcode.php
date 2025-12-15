<?php
/**
 * Shortcode for rendering the custom login UI.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Shortcode {

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
		add_shortcode( 'udi_custom_login', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode output.
	 *
	 * @param array  $atts Attributes.
	 * @param string $content Content (unused).
	 *
	 * @return string
	 */
	public function render_shortcode( $atts, $content = '' ) {
		if ( is_user_logged_in() ) {
			return $this->render_logged_in_message();
		}

		// Sanitize view parameter
		$view = 'login';
		if ( isset( $_GET['udi_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$view = sanitize_key( wp_unslash( $_GET['udi_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		
		$atts = shortcode_atts(
			array(
				'view' => $view,
			),
			$atts,
			'udi_custom_login'
		);

		return $this->plugin->renderer->render( $atts['view'], array( 'atts' => $atts ) );
	}

	/**
	 * Output message for logged-in users.
	 *
	 * @return string
	 */
	protected function render_logged_in_message() {
		$user = wp_get_current_user();
		$url  = udi_login_get_option( 'redirect_after_login', '' );
		if ( empty( $url ) && function_exists( 'wc_get_account_endpoint_url' ) ) {
			$url = wc_get_account_endpoint_url( 'dashboard' );
		}
		if ( empty( $url ) ) {
			$url = admin_url();
		}

		ob_start();
		?>
		<div class="udi-login-message udi-login-message--info">
			<p>
				<?php
				printf(
					/* translators: %s user display name */
					esc_html__( 'Olá %s, você já está conectado.', 'udi-custom-login' ),
					esc_html( $user->display_name )
				);
				?>
			</p>
			<a class="udi-btn" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Ir para minha conta', 'udi-custom-login' ); ?></a>
		</div>
		<?php
		return ob_get_clean();
	}
}
