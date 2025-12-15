<?php
/**
 * Custom WooCommerce login/register form (My Account).
 *
 * @package UDI_Custom_Login
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="udi-login-shortcode-wrapper">
	<?php echo do_shortcode( '[udi_custom_login]' ); ?>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
