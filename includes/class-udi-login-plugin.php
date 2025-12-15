<?php
/**
 * Main plugin bootstrap.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Plugin {

	/**
	 * Singleton.
	 *
	 * @var UDI_Login_Plugin
	 */
	protected static $instance;

	/**
	 * Settings handler.
	 *
	 * @var UDI_Login_Settings
	 */
	public $settings;

	/**
	 * Assets handler.
	 *
	 * @var UDI_Login_Assets
	 */
	public $assets;

	/**
	 * Form handler.
	 *
	 * @var UDI_Login_Form_Handler
	 */
	public $forms;

	/**
	 * Shortcode handler.
	 *
	 * @var UDI_Login_Shortcode
	 */
	public $shortcode;

	/**
	 * Renderer.
	 *
	 * @var UDI_Login_Renderer
	 */
	public $renderer;

	/**
	 * Security helper.
	 *
	 * @var UDI_Login_Security
	 */
	public $security;

	/**
	 * Google integration.
	 *
	 * @var UDI_Login_Google
	 */
	public $google;

	/**
	 * WooCommerce integration.
	 *
	 * @var UDI_Login_WooCommerce|null
	 */
	public $woocommerce;

	/**
	 * My Account customization handler.
	 *
	 * @var UDI_Login_My_Account|null
	 */
	public $my_account;

	/**
	 * Get singleton instance.
	 *
	 * @return UDI_Login_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->includes();
		$this->init_components();
		$this->hooks();
	}

	/**
	 * Include class files.
	 *
	 * @return void
	 */
	protected function includes() {
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/helpers.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/security-helpers.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-settings.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-assets.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-renderer.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-form-handler.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-shortcode.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-security.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-google.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-woocommerce.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-my-account.php';
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-security-logs.php';
		
		// Core
		if ( file_exists( UDI_LOGIN_PLUGIN_DIR . 'includes/Core/Validation.php' ) ) {
			require_once UDI_LOGIN_PLUGIN_DIR . 'includes/Core/Validation.php';
		}
	}

	/**
	 * Instantiate helpers.
	 *
	 * @return void
	 */
	protected function init_components() {
		$this->settings  = new UDI_Login_Settings();
		$this->assets    = new UDI_Login_Assets( $this );
		$this->renderer  = new UDI_Login_Renderer( $this );
		$this->forms     = new UDI_Login_Form_Handler( $this );
		$this->shortcode = new UDI_Login_Shortcode( $this );
		$this->security  = new UDI_Login_Security( $this );
		$this->google    = new UDI_Login_Google( $this );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'maybe_init_woocommerce' ), 20 );
		register_activation_hook( UDI_LOGIN_PLUGIN_FILE, array( 'UDI_Login_Plugin', 'activate' ) );
		register_deactivation_hook( UDI_LOGIN_PLUGIN_FILE, array( 'UDI_Login_Plugin', 'deactivate' ) );
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-installer.php';
		UDI_Login_Installer::activate();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Placeholder for future cleanup.
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'udi-custom-login', false, dirname( plugin_basename( UDI_LOGIN_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Initialize WooCommerce integration when plugin is active.
	 *
	 * @return void
	 */
	public function maybe_init_woocommerce() {
		if ( ! class_exists( 'WooCommerce' ) || $this->woocommerce ) {
			return;
		}

		$this->woocommerce = new UDI_Login_WooCommerce( $this );
		$this->my_account  = new UDI_Login_My_Account( $this );
	}
}
