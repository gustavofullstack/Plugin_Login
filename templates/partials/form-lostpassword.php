<?php
/**
 * Lost password form.
 *
 * @package UDI_Custom_Login
 */

$site_key = udi_login_get_option( 'recaptcha_site_key', '' );
?>
<form class="udi-form" method="post">
	<?php if ( method_exists( $this, 'the_honeypot_field' ) ) { $this->the_honeypot_field(); } ?>
	<p><?php esc_html_e( 'Informe o e-mail cadastrado e enviaremos um link para redefinir sua senha.', 'udi-custom-login' ); ?></p>
	<div class="udi-field">
		<label for="udi-lost-login"><?php esc_html_e( 'Email', 'udi-custom-login' ); ?></label>
		<input type="email" name="user_login" id="udi-lost-login" required />
	</div>

	<?php if ( $site_key && udi_login_get_option( 'enable_recaptcha', false ) ) : ?>
		<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
	<?php endif; ?>

	<input type="hidden" name="udi_form_action" value="lostpassword" />
	<?php wp_nonce_field( 'udi_lostpassword_action', 'udi_nonce' ); ?>

	<button type="submit" class="udi-btn udi-btn--primary">
		<?php esc_html_e( 'Enviar link', 'udi-custom-login' ); ?>
	</button>

	<p class="udi-form__footer">
		<a class="udi-link udi-switch" href="<?php echo esc_url( add_query_arg( 'udi_action', 'login' ) ); ?>" data-udi-view="login"><?php esc_html_e( 'Voltar ao login', 'udi-custom-login' ); ?></a>
	</p>
</form>
