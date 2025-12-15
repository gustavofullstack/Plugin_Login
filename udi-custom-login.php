<?php
/**
 * Plugin Name: UDI Custom Login
 * Description: Substitui todas as telas de autenticação do WordPress/WooCommerce por uma experiência customizada com design neon dark, segurança reforçada e configurações avançadas.
 * Version: 1.0.1
 * Author: Gustavo Mendes Almeida Rodrigues 
 * Text Domain: udi-custom-login
 * Domain Path: /languages
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UDI_LOGIN_VERSION', '1.0.1' );
define( 'UDI_LOGIN_PLUGIN_FILE', __FILE__ );
define( 'UDI_LOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UDI_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load emergency fix if it exists (to prevent redirect loops)
if ( file_exists( UDI_LOGIN_PLUGIN_DIR . 'emergency-fix.php' ) ) {
	require_once UDI_LOGIN_PLUGIN_DIR . 'emergency-fix.php';
}

require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-plugin.php';

UDI_Login_Plugin::instance();
