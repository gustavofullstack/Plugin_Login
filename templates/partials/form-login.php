<?php
defined( 'ABSPATH' ) || exit;

/**
 * Login form partial.
 *
 * @package UDI_Custom_Login
 */

$redirect = '';
if ( ! empty( $_GET['redirect_to'] ) ) {
	$redirect = esc_url( wp_unslash( $_GET['redirect_to'] ) );
}
$site_key = udi_login_get_option( 'recaptcha_site_key', '' );
?>
<form class="udi-form" method="post">
	<?php if ( method_exists( $this, 'the_honeypot_field' ) ) { $this->the_honeypot_field(); } ?>
	<div class="udi-field">
		<label for="udi-user_login"><?php esc_html_e( 'Email ou usuário', 'udi-custom-login' ); ?></label>
		<input type="text" name="user_login" id="udi-user_login" placeholder="<?php esc_attr_e( 'seuemail@dominio.com', 'udi-custom-login' ); ?>" required />
	</div>

	<div class="udi-field udi-field--password">
		<label for="udi-user_pass"><?php esc_html_e( 'Senha', 'udi-custom-login' ); ?></label>
		<div class="udi-password-wrapper">
			<input type="password" name="user_pass" id="udi-user_pass" placeholder="<?php esc_attr_e( '******', 'udi-custom-login' ); ?>" required />
			<button type="button" class="udi-toggle-password" aria-label="<?php esc_attr_e( 'Mostrar senha', 'udi-custom-login' ); ?>">
				<span class="udi-icon-eye"></span>
			</button>
		</div>
	</div>

	<div class="udi-form__meta">
		<label class="udi-checkbox">
			<input type="checkbox" name="rememberme" value="1" />
			<span><?php esc_html_e( 'Manter-me conectado', 'udi-custom-login' ); ?></span>
		</label>
		<a class="udi-link" href="<?php echo esc_url( add_query_arg( 'udi_action', 'lostpassword' ) ); ?>"><?php esc_html_e( 'Esqueci minha senha', 'udi-custom-login' ); ?></a>
	</div>

	<?php if ( $site_key && udi_login_get_option( 'enable_recaptcha', false ) ) : ?>
		<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
	<?php endif; ?>

	<input type="hidden" name="udi_form_action" value="login" />
	<?php if ( $redirect ) : ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>" />
	<?php endif; ?>
	<?php wp_nonce_field( 'udi_login_action', 'udi_nonce' ); ?>

	<button type="submit" class="udi-btn udi-btn--primary">
		<?php esc_html_e( 'Entrar', 'udi-custom-login' ); ?>
	</button>

	<?php
	// Render Google Sign-In button
	if ( has_action( 'udi_login_after_submit_button' ) ) {
		do_action( 'udi_login_after_submit_button', 'login' );
	}
	?>

	<?php if ( get_option( 'users_can_register' ) ) : ?>
		<p class="udi-form__footer">
			<?php esc_html_e( 'Ainda não possui conta?', 'udi-custom-login' ); ?>
			<a class="udi-link udi-switch" href="<?php echo esc_url( add_query_arg( 'udi_action', 'register' ) ); ?>" data-udi-view="register"><?php esc_html_e( 'Cadastre-se', 'udi-custom-login' ); ?></a>
		</p>
	<?php endif; ?>
</form>
