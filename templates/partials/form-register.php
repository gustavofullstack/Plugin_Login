<?php
defined( 'ABSPATH' ) || exit;

/**
 * Register form partial.
 *
 * @package UDI_Custom_Login
 */

$site_key = udi_login_get_option( 'recaptcha_site_key', '' );
?>
<form class="udi-form" method="post">
	<?php if ( method_exists( $this, 'the_honeypot_field' ) ) { $this->the_honeypot_field(); } ?>
	<div class="udi-field">
		<label for="udi-first_name"><?php esc_html_e( 'Nome', 'udi-custom-login' ); ?></label>
		<input type="text" name="first_name" id="udi-first_name" placeholder="<?php esc_attr_e( 'Seu nome completo', 'udi-custom-login' ); ?>" />
	</div>

	<div class="udi-field">
		<label for="udi-user_email"><?php esc_html_e( 'Email', 'udi-custom-login' ); ?></label>
		<input type="email" name="user_email" id="udi-user_email" placeholder="email@dominio.com" required />
	</div>

	<div class="udi-field udi-field--password">
		<label for="udi-register_pass"><?php esc_html_e( 'Senha', 'udi-custom-login' ); ?></label>
		<div class="udi-password-wrapper">
			<input type="password" name="user_pass" id="udi-register_pass" placeholder="<?php esc_attr_e( 'Mínimo 6 caracteres', 'udi-custom-login' ); ?>" required minlength="6" />
			<button type="button" class="udi-toggle-password" aria-label="<?php esc_attr_e( 'Mostrar senha', 'udi-custom-login' ); ?>">
				<span class="udi-icon-eye"></span>
			</button>
		</div>
		<small class="udi-password-hint"><?php esc_html_e( 'Use letras maiúsculas, minúsculas e números para mais segurança.', 'udi-custom-login' ); ?></small>
	</div>

	<?php if ( $site_key && udi_login_get_option( 'enable_recaptcha', false ) ) : ?>
		<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>
	<?php endif; ?>

	<?php do_action( 'udi_login_social_buttons', 'register' ); ?>

	<input type="hidden" name="udi_form_action" value="register" />
	<?php wp_nonce_field( 'udi_register_action', 'udi_nonce' ); ?>
	<button type="submit" class="udi-btn udi-btn--primary">
		<?php esc_html_e( 'Criar conta', 'udi-custom-login' ); ?>
	</button>

	<p class="udi-form__footer">
		<?php esc_html_e( 'Já possui cadastro?', 'udi-custom-login' ); ?>
		<a class="udi-link udi-switch" href="<?php echo esc_url( add_query_arg( 'udi_action', 'login' ) ); ?>" data-udi-view="login"><?php esc_html_e( 'Fazer login', 'udi-custom-login' ); ?></a>
	</p>
</form>
