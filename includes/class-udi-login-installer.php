<?php
/**
 * Activation helper.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Installer {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! function_exists( 'udi_login_option_name' ) ) {
			require_once UDI_LOGIN_PLUGIN_DIR . 'includes/helpers.php';
		}

		self::maybe_create_login_page();
		self::maybe_create_account_page();
		if ( class_exists( 'UDI_Login_My_Account' ) ) {
			UDI_Login_My_Account::add_history_endpoint();
		}
		self::create_tables();
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 *
	 * @return void
	 */
	protected static function create_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'udi_security_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			event_type varchar(50) NOT NULL,
			message text NOT NULL,
			ip_address varchar(45) NOT NULL,
			ip_hash char(64) NOT NULL,
			user_id bigint(20) unsigned DEFAULT 0,
			meta_json longtext DEFAULT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY event_type (event_type),
			KEY ip_hash (ip_hash),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Create login page with shortcode if needed.
	 *
	 * @return void
	 */
	protected static function maybe_create_login_page() {
		$settings = get_option( udi_login_option_name(), array() );
		$page_id  = isset( $settings['login_page_id'] ) ? absint( $settings['login_page_id'] ) : 0;

		if ( $page_id && get_post_status( $page_id ) ) {
			return;
		}

		$page = get_page_by_path( 'entrar' );

		if ( $page ) {
			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_title'   => __( 'Entrar', 'udi-custom-login' ),
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_content' => '[udi_custom_login]',
				)
			);
		}

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			return;
		}

		$settings['login_page_id'] = $page_id;
		update_option( udi_login_option_name(), $settings );
	}

	/**
	 * Ensure a WooCommerce "Minha Conta" page exists.
	 *
	 * @return void
	 */
	protected static function maybe_create_account_page() {
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

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			return;
		}

		update_option( 'woocommerce_myaccount_page_id', $page_id );
	}
}
