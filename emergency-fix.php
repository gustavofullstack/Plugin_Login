<?php
/**
 * EMERGENCY FIX - Disable redirect loop
 * 
 * This file temporarily disables the custom login redirect to fix ERR_TOO_MANY_REDIRECTS
 * 
 * Upload this file to: /wp-content/plugins/Plugin_Login/
 * Access your site again
 * Then DELETE this file
 */

// Hook very early before plugin init
add_filter( 'option_udi_login_settings', 'udi_emergency_fix_redirect_loop', 1 );

function udi_emergency_fix_redirect_loop( $settings ) {
    if ( ! is_array( $settings ) ) {
        $settings = array();
    }
    
    // Force disable custom login redirect
    $settings['enable_custom_login'] = false;
    
    return $settings;
}

// Admin notice - DISABLED to prevent showing on login page
// add_action( 'admin_notices', 'udi_emergency_fix_notice' );

function udi_emergency_fix_notice() {
    // Only show in admin area, not on login page
    if ( ! is_admin() ) {
        return;
    }
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><strong>UDI Login - Emergency Fix Active!</strong></p>
        <p>The redirect loop has been fixed. Custom login redirect is temporarily disabled.</p>
        <p><strong>Action required:</strong> DELETE this file: <code>/wp-content/plugins/Plugin_Login/emergency-fix.php</code></p>
    </div>
    <?php
}
