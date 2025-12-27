<?php
/**
 * Plugin Name: UDI Custom Login
 * Description: Substitui todas as telas de autenticação do WordPress/WooCommerce por uma experiência customizada com design neon dark, segurança reforçada e configurações avançadas.
 * Version: 1.0.5
 * Author: Gustavo Mendes Almeida Rodrigues 
 * Text Domain: udi-custom-login
 * Domain Path: /languages
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TriqHub Invisible Connector
if ( ! class_exists( 'TriqHub_Connector' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/core/class-triqhub-connector.php';
    new TriqHub_Connector( 'TRQ-INVISIBLE-KEY', 'Plugin_Login' );
}

define( 'UDI_LOGIN_VERSION', '1.0.5' );
define( 'UDI_LOGIN_PLUGIN_FILE', __FILE__ );
define( 'UDI_LOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UDI_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );



// Load Composer autoloader
if ( file_exists( UDI_LOGIN_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once UDI_LOGIN_PLUGIN_DIR . 'vendor/autoload.php';
}


require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-plugin.php';

// Initialize Plugin Update Checker
if ( file_exists( UDI_LOGIN_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once UDI_LOGIN_PLUGIN_DIR . 'vendor/autoload.php';
    
    // Only initialize if the class exists (composer installed)
    if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
        $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            'https://github.com/gustavofullstack/Plugin_Login',
            __FILE__,
            'udi-custom-login'
        );
        $myUpdateChecker->setBranch('main');
        
        // Configuração de Token para repositórios privados
        // Defina UDI_LOGIN_GITHUB_TOKEN no wp-config.php para maior segurança
        if ( defined( 'UDI_LOGIN_GITHUB_TOKEN' ) ) {
            $myUpdateChecker->setAuthentication( UDI_LOGIN_GITHUB_TOKEN );
        }

    }
}

UDI_Login_Plugin::instance();

// Schedule Garbage Collection
register_activation_hook( __FILE__, 'udi_login_schedule_gc' );
register_deactivation_hook( __FILE__, 'udi_login_unschedule_gc' );

function udi_login_schedule_gc() {
	if ( ! wp_next_scheduled( 'udi_login_daily_gc' ) ) {
		wp_schedule_event( time(), 'daily', 'udi_login_daily_gc' );
	}
	// Also trigger installer
	require_once UDI_LOGIN_PLUGIN_DIR . 'includes/class-udi-login-installer.php';
	UDI_Login_Installer::activate();
}

function udi_login_unschedule_gc() {
	wp_clear_scheduled_hook( 'udi_login_daily_gc' );
}

add_action( 'udi_login_daily_gc', 'udi_login_gc_logs' );
