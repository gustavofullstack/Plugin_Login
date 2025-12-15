<?php
/**
 * Plugin Validation Class.
 *
 * Handles self-diagnosis and validation of the plugin environment.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UDI_Login_Validation {

	/**
	 * Run full validation suite.
	 *
	 * @return array Results of validation.
	 */
	public static function run_full_validation() {
		$results = array(
			'passed'   => 0,
			'failed'   => 0,
			'warnings' => 0,
			'messages' => array(),
		);

		self::check_files( $results );
		self::check_directories( $results );
		self::check_classes( $results );
		
		return $results;
	}

	/**
	 * Check critical files existence.
	 *
	 * @param array $results Reference to results array.
	 */
	private static function check_files( &$results ) {
		$files = array(
			'udi-custom-login.php',
			'includes/helpers.php',
			'includes/security-helpers.php',
		);

		foreach ( $files as $file ) {
			if ( file_exists( UDI_LOGIN_PLUGIN_DIR . $file ) ) {
				$results['passed']++;
				$results['messages'][] = array( 'type' => 'success', 'msg' => "File found: $file" );
			} else {
				$results['failed']++;
				$results['messages'][] = array( 'type' => 'error', 'msg' => "File missing: $file" );
			}
		}
	}

	/**
	 * Check directories.
	 * 
	 * @param array $results Reference to results array.
	 */
	private static function check_directories( &$results ) {
		$dirs = array(
			'includes',
			'templates',
			'assets',
		);

		foreach ( $dirs as $dir ) {
			if ( is_dir( UDI_LOGIN_PLUGIN_DIR . $dir ) ) {
				$results['passed']++;
			} else {
				$results['failed']++;
				$results['messages'][] = array( 'type' => 'error', 'msg' => "Directory missing: $dir" );
			}
		}
	}

	/**
	 * Check if core classes exist.
	 *
	 * @param array $results Reference to results array.
	 */
	private static function check_classes( &$results ) {
		$classes = array(
			'UDI_Login_Plugin',
			'UDI_Login_Settings',
			'UDI_Login_Security',
		);

		foreach ( $classes as $class ) {
			if ( class_exists( $class ) ) {
				$results['passed']++;
			} else {
				// Class might not be loaded yet if we run this too early, but it's a check
				$results['warnings']++;
				$results['messages'][] = array( 'type' => 'warning', 'msg' => "Class not loaded: $class" );
			}
		}
	}
}
