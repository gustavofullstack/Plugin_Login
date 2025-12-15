<?php
/**
 * Main login layout used by shortcode/page overrides.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view      = isset( $data['view'] ) ? $data['view'] : 'login';
$settings  = isset( $data['settings'] ) ? $data['settings'] : udi_login_get_settings();
$messages  = isset( $data['messages'] ) ? $data['messages'] : array();
$logo_id   = ! empty( $settings['logo_id'] ) ? (int) $settings['logo_id'] : 0;
$bg_id     = ! empty( $settings['background_id'] ) ? (int) $settings['background_id'] : 0;
$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$bg_url    = $bg_id ? wp_get_attachment_image_url( $bg_id, 'full' ) : '';
$headline  = $settings['headline'] ? $settings['headline'] : __( 'Bem-vindo de volta', 'udi-custom-login' );
$sub       = $settings['subheadline'] ? $settings['subheadline'] : __( 'Acesse sua conta e continue aproveitando ofertas exclusivas.', 'udi-custom-login' );
$site_key  = udi_login_get_option( 'recaptcha_site_key', '' );
$registration_open = (bool) get_option( 'users_can_register' );

if ( 'register' === $view && ! $registration_open ) {
	$view = 'login';
}

?>
<div class="udi-login-viewport <?php echo esc_attr( 'udi-view-' . $view ); ?>"<?php echo $bg_url ? ' style="background-image:url(' . esc_url( $bg_url ) . ')"' : ''; ?>>
	<div class="udi-login-overlay"></div>
	<div class="udi-login-card">
		<div class="udi-login-card__intro">
			<?php if ( $logo_url ) : ?>
				<div class="udi-login-logo">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Logo', 'udi-custom-login' ); ?>" loading="lazy" />
				</div>
			<?php endif; ?>
			<h1><?php echo esc_html( $headline ); ?></h1>
			<p><?php echo esc_html( $sub ); ?></p>
			<?php if ( ! empty( $settings['social_login_note'] ) ) : ?>
				<div class="udi-login-note"><?php echo wp_kses_post( wpautop( $settings['social_login_note'] ) ); ?></div>
			<?php endif; ?>
			<?php do_action( 'udi_login_intro', $view, $settings ); ?>
		</div>
		<div class="udi-login-card__form">
			<div class="udi-login-messages" aria-live="polite">
				<?php foreach ( $messages as $notice ) : ?>
					<div class="udi-login-message udi-login-message--<?php echo esc_attr( $notice['type'] ); ?>">
						<?php echo wp_kses_post( $notice['message'] ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="udi-login-forms" data-current-view="<?php echo esc_attr( $view ); ?>">
				<?php echo $renderer->get_form_html( $view ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<?php if ( $site_key && udi_login_get_option( 'enable_recaptcha', false ) ) : ?>
				<p class="udi-login-recaptcha-note">
					<?php esc_html_e( 'Este site é protegido por reCAPTCHA e se aplica a Política de Privacidade e os Termos de Serviço do Google.', 'udi-custom-login' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php do_action( 'udi_login_after_card', $view, $settings ); ?>
</div>
