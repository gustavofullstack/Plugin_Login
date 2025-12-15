<?php
/**
 * Helper functions.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'udi_login_option_name' ) ) {
	/**
	 * Option name used for plugin settings.
	 *
	 * @return string
	 */
	function udi_login_option_name() {
		return 'udi_login_settings';
	}
}

if ( ! function_exists( 'udi_login_get_settings' ) ) {
	/**
	 * Retrieve plugin settings array with defaults.
	 *
	 * @return array
	 */
	function udi_login_get_settings() {
		$defaults = array(
			'enable_custom_login'      => true,
			'login_page_id'            => 0,
			'logo_id'                  => 0,
			'background_id'            => 0,
			'headline'                 => '',
			'subheadline'              => '',
			'redirect_after_login'     => '',
			'redirect_after_logout'    => '',
			'redirect_after_register'  => '',
			'enable_recaptcha'         => false,
			'recaptcha_site_key'       => '',
			'recaptcha_secret_key'     => '',
			'limit_login_enabled'      => true,
			'limit_login_attempts'     => 5,
			'limit_login_lockout'      => 15,
			'social_login_note'        => '',
			'woocommerce_styling'      => true,
			'my_account_customization' => true,
			'my_account_history_endpoint' => true,
			'my_account_history_label' => 'Histórico',
			'my_account_remove_downloads' => true,
			// Security options
			'enable_security_logging'  => false,
			'enable_password_strength' => false,
			'password_min_length'      => 8,
			'password_min_score'       => 2,
			// Google Sign-In options
			'enable_google_signin'     => true,
			'google_client_id'         => '380224261904-8vl2tsvutfe0gtccg93aspi0ha4rtn5l.apps.googleusercontent.com',
			'google_enable_fedcm'      => true,
			'google_enable_onetap'     => true,
			'google_enable_auto_select' => false,
			'google_button_type'       => 'standard',
			'google_button_theme'      => 'filled_blue',
			'google_button_size'       => 'large',
			'google_button_text'       => 'signin_with',
		);

		$settings = get_option( udi_login_option_name(), array() );

		return wp_parse_args( $settings, $defaults );
	}
}

if ( ! function_exists( 'udi_login_get_option' ) ) {
	/**
	 * Helper to fetch an option.
	 *
	 * @param string $key Key to retrieve.
	 * @param mixed  $default Default fallback.
	 *
	 * @return mixed
	 */
	function udi_login_get_option( $key, $default = null ) {
		$settings = udi_login_get_settings();

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}

if ( ! function_exists( 'udi_login_array_get' ) ) {
	/**
	 * Safe array getter.
	 *
	 * @param array  $array Array.
	 * @param string $key Key.
	 * @param mixed  $default Default.
	 *
	 * @return mixed
	 */
	function udi_login_array_get( $array, $key, $default = null ) {
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}
}
