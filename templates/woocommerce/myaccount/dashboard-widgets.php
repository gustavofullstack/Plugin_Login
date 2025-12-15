<?php
/**
 * Dashboard quick links.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $links ) ) {
	return;
}
?>
<div class="udi-account-grid">
	<?php foreach ( $links as $link ) : ?>
		<a class="udi-account-card" href="<?php echo esc_url( $link['url'] ); ?>">
			<h3><?php echo esc_html( $link['label'] ); ?></h3>
			<p><?php echo esc_html( $link['description'] ); ?></p>
		</a>
	<?php endforeach; ?>
</div>
