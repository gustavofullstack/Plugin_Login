<?php
/**
 * Customizations for WooCommerce My Account area.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_My_Account {

	const HISTORY_ENDPOINT = 'udi-history';

	/**
	 * Plugin reference.
	 *
	 * @var UDI_Login_Plugin
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @param UDI_Login_Plugin $plugin Plugin.
	 */
	public function __construct( UDI_Login_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'init', array( $this, 'ensure_account_page_exists' ), 5 );
		add_action( 'init', array( $this, 'register_history_endpoint' ), 15 );
		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'filter_menu_items' ), 12 );
		add_action( 'woocommerce_account_' . self::HISTORY_ENDPOINT . '_endpoint', array( $this, 'render_history_endpoint' ) );
		add_action( 'woocommerce_account_dashboard', array( $this, 'render_dashboard_cards' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Create or restore the WooCommerce "Minha Conta" page after rewrite rules are loaded.
	 *
	 * @return void
	 */
	public function ensure_account_page_exists() {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return;
		}

		$page_id = wc_get_page_id( 'myaccount' );

		if ( $page_id && 'trash' !== get_post_status( $page_id ) ) {
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Minha Conta', 'udi-custom-login' ),
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[woocommerce_my_account]',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( 'woocommerce_myaccount_page_id', $page_id );
		}
	}

	/**
	 * Whether feature toggle is active.
	 *
	 * @return bool
	 */
	protected function is_enabled() {
		return (bool) udi_login_get_option( 'my_account_customization', true );
	}

	/**
	 * If history endpoint is active.
	 *
	 * @return bool
	 */
	protected function is_history_enabled() {
		return $this->is_enabled() && (bool) udi_login_get_option( 'my_account_history_endpoint', true );
	}

	/**
	 * Register endpoint for recently viewed products.
	 *
	 * @return void
	 */
	public function register_history_endpoint() {
		if ( ! $this->is_history_enabled() ) {
			return;
		}

		self::add_history_endpoint();
	}

	/**
	 * Static helper to expose endpoint registration (used on activation).
	 *
	 * @return void
	 */
	public static function add_history_endpoint() {
		add_rewrite_endpoint( self::HISTORY_ENDPOINT, EP_ROOT | EP_PAGES );
	}

	/**
	 * Register query var for custom endpoint.
	 *
	 * @param array $vars Query vars.
	 *
	 * @return array
	 */
	public function register_query_var( $vars ) {
		if ( $this->is_history_enabled() && ! in_array( self::HISTORY_ENDPOINT, $vars, true ) ) {
			$vars[] = self::HISTORY_ENDPOINT;
		}

		return $vars;
	}

	/**
	 * Customize menu items.
	 *
	 * @param array $items Menu items.
	 *
	 * @return array
	 */
	public function filter_menu_items( $items ) {
		if ( ! $this->is_enabled() ) {
			return $items;
		}

		$labels = array(
			'dashboard'        => __( 'Painel', 'udi-custom-login' ),
			'orders'           => __( 'Meus pedidos', 'udi-custom-login' ),
			'edit-address'     => __( 'Endereços', 'udi-custom-login' ),
			'edit-account'     => __( 'Detalhes da conta', 'udi-custom-login' ),
			'customer-logout'  => __( 'Sair', 'udi-custom-login' ),
		);

		foreach ( $labels as $key => $label ) {
			if ( isset( $items[ $key ] ) ) {
				$items[ $key ] = $label;
			}
		}

		if ( udi_login_get_option( 'my_account_remove_downloads', true ) && isset( $items['downloads'] ) ) {
			unset( $items['downloads'] );
		}

		if ( $this->is_history_enabled() ) {
			$items[ self::HISTORY_ENDPOINT ] = udi_login_get_option( 'my_account_history_label', __( 'Histórico', 'udi-custom-login' ) );
		}

		$order = array(
			'dashboard',
			'orders',
			self::HISTORY_ENDPOINT,
			'edit-address',
			'edit-account',
		);

		$ordered = array();

		foreach ( $order as $endpoint ) {
			if ( isset( $items[ $endpoint ] ) ) {
				$ordered[ $endpoint ] = $items[ $endpoint ];
				unset( $items[ $endpoint ] );
			}
		}

		foreach ( $items as $key => $value ) {
			$ordered[ $key ] = $value;
		}

		// Ensure logout is at the bottom.
		if ( isset( $ordered['customer-logout'] ) ) {
			$logout = $ordered['customer-logout'];
			unset( $ordered['customer-logout'] );
			$ordered['customer-logout'] = $logout;
		}

		return $ordered;
	}

	/**
	 * Enqueue custom CSS for My Account page.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! function_exists( 'is_account_page' ) || ! is_account_page() || ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_style( 'udi-my-account' );
	}

	/**
	 * Render the dashboard quick links/cards.
	 *
	 * @return void
	 */
	public function render_dashboard_cards() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$links = array(
			array(
				'label'       => __( 'Pedidos', 'udi-custom-login' ),
				'description' => __( 'Acompanhe entregas e detalhes das suas compras.', 'udi-custom-login' ),
				'url'         => wc_get_account_endpoint_url( 'orders' ),
			),
			array(
				'label'       => __( 'Endereços', 'udi-custom-login' ),
				'description' => __( 'Atualize endereço de cobrança e entrega.', 'udi-custom-login' ),
				'url'         => wc_get_account_endpoint_url( 'edit-address' ),
			),
			array(
				'label'       => __( 'Dados da conta', 'udi-custom-login' ),
				'description' => __( 'Altere nome, email ou senha.', 'udi-custom-login' ),
				'url'         => wc_get_account_endpoint_url( 'edit-account' ),
			),
		);

		wc_get_template(
			'myaccount/dashboard-widgets.php',
			array(
				'links' => $links,
			),
			'',
			UDI_LOGIN_PLUGIN_DIR . 'templates/woocommerce/'
		);
	}

	/**
	 * Render products seen recently.
	 *
	 * @return void
	 */
	public function render_history_endpoint() {
		if ( ! $this->is_history_enabled() ) {
			return;
		}

		$products = $this->get_recently_viewed_products();

		wc_get_template(
			'myaccount/history.php',
			array(
				'products' => $products,
			),
			'',
			UDI_LOGIN_PLUGIN_DIR . 'templates/woocommerce/'
		);
	}

	/**
	 * Get recently viewed WooCommerce products.
	 *
	 * @return array<WC_Product>
	 */
	protected function get_recently_viewed_products() {
		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) {
			return array();
		}

		// Sanitize and validate cookie data
		$viewed = isset( $_COOKIE['woocommerce_recently_viewed'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : '';
		
		if ( empty( $viewed ) ) {
			return array();
		}
		
		// Only allow numeric IDs separated by pipes
		$ids = array_reverse( array_filter( array_map( 'absint', explode( '|', $viewed ) ) ) );

		if ( empty( $ids ) ) {
			return array();
		}

		$ids = array_slice( array_unique( $ids ), 0, 8 );

		return wc_get_products(
			array(
				'include' => $ids,
				'orderby' => 'post__in',
				'limit'   => count( $ids ),
			)
		);
	}

}
