<?php
/**
 * Page template for the dedicated login page.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="udi-login-page" role="main">
	<?php echo do_shortcode( '[udi_custom_login]' ); ?>
</main>
<?php
get_footer();
