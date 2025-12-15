<?php
defined( 'ABSPATH' ) || exit;

/**
 * Reset password form.
 *
 * @package UDI_Custom_Login
 */

$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$login = isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
?>
<form class="udi-form" method="post">
	<?php if ( method_exists( $this, 'the_honeypot_field' ) ) { $this->the_honeypot_field(); } ?>
	<p><?php esc_html_e( 'Crie uma nova senha para sua conta.', 'udi-custom-login' ); ?></p>

	<div class="udi-field">
		<label for="udi-pass1"><?php esc_html_e( 'Nova senha', 'udi-custom-login' ); ?></label>
		<input type="password" name="pass1" id="udi-pass1" required minlength="6" />
	</div>

	<div class="udi-field">
		<label for="udi-pass2"><?php esc_html_e( 'Confirme a nova senha', 'udi-custom-login' ); ?></label>
		<input type="password" name="pass2" id="udi-pass2" required minlength="6" />
	</div>

	<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>" />
	<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>" />
	<input type="hidden" name="udi_form_action" value="resetpass" />
	<?php wp_nonce_field( 'udi_resetpass_action', 'udi_nonce' ); ?>

	<button type="submit" class="udi-btn udi-btn--primary">
		<?php esc_html_e( 'Redefinir senha', 'udi-custom-login' ); ?>
	</button>

	<p class="udi-form__footer">
		<a class="udi-link udi-switch" href="<?php echo esc_url( add_query_arg( 'udi_action', 'login' ) ); ?>" data-udi-view="login"><?php esc_html_e( 'Voltar ao login', 'udi-custom-login' ); ?></a>
	</p>
</form>
