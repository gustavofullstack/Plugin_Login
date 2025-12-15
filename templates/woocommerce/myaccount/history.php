<?php
/**
 * Recently viewed products endpoint.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php echo esc_html( udi_login_get_option( 'my_account_history_label', __( 'Histórico', 'udi-custom-login' ) ) ); ?></h2>

<?php if ( empty( $products ) ) : ?>
	<div class="udi-history-empty">
		<?php esc_html_e( 'Ainda não há produtos no seu histórico. Explore a loja e volte para ver recomendações.', 'udi-custom-login' ); ?>
	</div>
<?php else : ?>
	<div class="udi-history-list">
		<?php foreach ( $products as $product ) : ?>
			<div class="udi-history-item">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
					<span><?php echo esc_html( $product->get_name() ); ?></span>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
