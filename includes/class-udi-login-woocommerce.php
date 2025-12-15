<?php
/**
 * WooCommerce integration.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_WooCommerce {

	/**
	 * Plugin.
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

		add_filter( 'woocommerce_locate_template', array( $this, 'override_login_template' ), 10, 3 );
		add_action( 'woocommerce_before_customer_login_form', array( $this, 'before_wc_login_form' ) );
		add_action( 'woocommerce_after_customer_login_form', array( $this, 'after_wc_login_form' ) );
	}

	/**
	 * Override Woo template with plugin version.
	 *
	 * @param string $template      Default template path.
	 * @param string $template_name Template name.
	 * @param string $template_path Template path.
	 *
	 * @return string
	 */
	public function override_login_template( $template, $template_name, $template_path ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( 'myaccount/form-login.php' !== $template_name ) {
			return $template;
		}

		if ( ! udi_login_get_option( 'woocommerce_styling', true ) ) {
			return $template;
		}

		$custom = UDI_LOGIN_PLUGIN_DIR . 'templates/woocommerce/form-login.php';

		if ( file_exists( $custom ) ) {
			return $custom;
		}

		return $template;
	}

	/**
	 * Print wrapper before Woo login form.
	 *
	 * @return void
	 */
	public function before_wc_login_form() {
		echo '<div class="udi-woocommerce-login">';
	}

	/**
	 * Close wrapper.
	 *
	 * @return void
	 */
	public function after_wc_login_form() {
		echo '</div>';
	}
}
