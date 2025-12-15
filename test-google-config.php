<?php
/**
 * Test script to verify Google Sign-In configuration
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

echo "=== UDI Login - Google Sign-In Configuration Test ===\n\n";

// Get plugin settings
$settings = get_option( 'udi_login_settings', array() );

echo "1. Google Sign-In Enabled: " . ( ! empty( $settings['enable_google_signin'] ) ? 'YES ✓' : 'NO ✗' ) . "\n";
echo "2. Google Client ID: " . ( ! empty( $settings['google_client_id'] ) ? 'SET ✓' : 'EMPTY ✗' ) . "\n";

if ( ! empty( $settings['google_client_id'] ) ) {
    $client_id = $settings['google_client_id'];
    echo "   Client ID: " . substr( $client_id, 0, 20 ) . "..." . substr( $client_id, -20 ) . "\n";
}

echo "3. FedCM Enabled: " . ( ! empty( $settings['google_enable_fedcm'] ) ? 'YES ✓' : 'NO ✗' ) . "\n";
echo "4. One Tap Enabled: " . ( ! empty( $settings['google_enable_onetap'] ) ? 'YES ✓' : 'NO ✗' ) . "\n";
echo "5. Button Theme: " . ( $settings['google_button_theme'] ?? 'not set' ) . "\n";
echo "6. Button Size: " . ( $settings['google_button_size'] ?? 'not set' ) . "\n";

echo "\n=== Testing if Google button should render ===\n\n";

// Check UDI_Login_Google class
if ( class_exists( 'UDI_Login_Google' ) ) {
    echo "✓ UDI_Login_Google class exists\n";
    
    // Try to instantiate (we need the plugin instance)
    if ( class_exists( 'UDI_Login_Plugin' ) ) {
        $plugin = UDI_Login_Plugin::get_instance();
        
        if ( isset( $plugin->google ) && $plugin->google instanceof UDI_Login_Google ) {
            echo "✓ Google Sign-In component is loaded\n";
            
            $is_enabled = $plugin->google->is_enabled();
            echo "✓ is_enabled() returns: " . ( $is_enabled ? 'TRUE' : 'FALSE' ) . "\n";
            
            if ( ! $is_enabled ) {
                echo "✗ Google Sign-In is NOT enabled - button will NOT render\n";
            } else {
                echo "✓ Google Sign-In IS enabled - button SHOULD render\n";
            }
        } else {
            echo "✗ Google component not properly initialized\n";
        }
    }
} else {
    echo "✗ UDI_Login_Google class not found\n";
}

echo "\n=== Admin Save Button Test ===\n\n";

// Check if submit_button function exists
if ( function_exists( 'submit_button' ) ) {
    echo "✓ submit_button() function exists (admin save button should work)\n";
} else {
    echo "✗ submit_button() function not found\n";
}

echo "\nTest complete.\n";
