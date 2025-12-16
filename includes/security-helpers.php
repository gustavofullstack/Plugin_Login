<?php
/**
 * Security logging helper.
 *
 * @package UDI_Custom_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log security events.
 *
 * @param string $event_type Type of event (login_failed, login_success, account_locked, etc).
 * @param string $message    Event message.
 * @param array  $context    Additional context data.
 *
 * @return void
 */
function udi_login_log_security_event( $event_type, $message, $context = array() ) {
	if ( ! udi_login_get_option( 'enable_security_logging', false ) ) {
		return;
	}

	global $wpdb;
	
	$ip = udi_login_get_client_ip();
	// Anonymized IP hash for aggregation/privacy
	$ip_hash = hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	
	// Create a masked IP for display (Privacy Friendly)
	$ip_masked = $ip;
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		$ip_masked = preg_replace( '/(\d+)\.(\d+)\.(\d+)\.(\d+)/', '$1.$2.$3.xxx', $ip );
	} elseif ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
		$parts = explode( ':', $ip );
		if ( count( $parts ) > 4 ) {
			$ip_masked = implode( ':', array_slice( $parts, 0, 4 ) ) . ':*:*:*:*';
		}
	}
	
	$user_id = 0;
	if ( isset( $context['user_id'] ) ) {
		$user_id = absint( $context['user_id'] );
	} elseif ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	$table_name = $wpdb->prefix . 'udi_security_logs';

	// Check if table exists (fail-safe for initial migration)
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
		return; 
	}

	$wpdb->insert(
		$table_name,
		array(
			'event_type' => sanitize_key( $event_type ),
			'message'    => sanitize_text_field( $message ),
			'ip_address' => $ip_masked, // Store MASKED IP for privacy
			'ip_hash'    => $ip_hash,
			'user_id'    => $user_id,
			'meta_json'  => wp_json_encode( $context ),
			'created_at' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	// Optionally log to error_log for critical events
	if ( in_array( $event_type, array( 'account_locked', 'suspicious_activity' ), true ) ) {
		error_log( sprintf( '[UDI Login Security] %s: %s (IP Hash: %s)', $event_type, $message, $ip_hash ) );
	}

	do_action( 'udi_login_security_event_logged', $event_type, $message, $context );
}

/**
 * Get client IP address with strict Proxy/Cloudflare support.
 *
 * @return string
 */
function udi_login_get_client_ip() {
	$ip = '';

	// 1. Cloudflare (Trusted Header)
	// Only trust if header is present. Ideally we should verifiy REMOTE_ADDR is Cloudflare, 
	// but for this plugin context, prioritizing this header is the standard "Fix".
	if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	} 
	// 2. Trusted Proxy (Opt-in ONLY)
	// We do NOT trust X-Forwarded-For by default to avoid spoofing.
	elseif ( defined( 'UDI_TRUST_PROXY' ) && UDI_TRUST_PROXY && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$ip = trim( $ips[0] );
	} 
	
	// 3. Direct Connection (Fallback)
	if ( empty( $ip ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Validate password strength.
 *
 * @param string $password Password to check.
 *
 * @return array {
 *     Validation result.
 *
 *     @type bool   $valid    Whether password is strong enough.
 *     @type string $message  Error message if invalid.
 *     @type int    $score    Strength score (0-4).
 * }
 */
function udi_login_validate_password_strength( $password ) {
	if ( ! udi_login_get_option( 'enable_password_strength', false ) ) {
		return array(
			'valid'   => true,
			'message' => '',
			'score'   => 0,
		);
	}

	$score   = 0;
	$message = '';

	// Minimum length check
	$min_length = (int) udi_login_get_option( 'password_min_length', 8 );
	if ( strlen( $password ) < $min_length ) {
		return array(
			'valid'   => false,
			'message' => sprintf(
				/* translators: %d: minimum password length */
				__( 'A senha deve ter no mínimo %d caracteres.', 'udi-custom-login' ),
				$min_length
			),
			'score'   => 0,
		);
	}

	// Score based on complexity
	if ( strlen( $password ) >= 8 ) {
		$score++;
	}
	if ( strlen( $password ) >= 12 ) {
		$score++;
	}
	if ( preg_match( '/[a-z]/', $password ) && preg_match( '/[A-Z]/', $password ) ) {
		$score++; // Has both lowercase and uppercase
	}
	if ( preg_match( '/[0-9]/', $password ) ) {
		$score++; // Has numbers
	}
	if ( preg_match( '/[^a-zA-Z0-9]/', $password ) ) {
		$score++; // Has special characters
	}

	// Common password check
	$common_passwords = array(
		'123456', 'password', '12345678', 'qwerty', '123456789', '12345', '1234', '111111',
		'1234567', 'dragon', '123123', 'baseball', 'iloveyou', 'trustno1', '1234567890',
		'senha', 'senha123', 'admin', 'admin123',
	);

	if ( in_array( strtolower( $password ), $common_passwords, true ) ) {
		return array(
			'valid'   => false,
			'message' => __( 'Esta senha é muito comum. Escolha uma senha mais forte.', 'udi-custom-login' ),
			'score'   => 0,
		);
	}

	// Require minimum score
	$min_score = (int) udi_login_get_option( 'password_min_score', 2 );
	if ( $score < $min_score ) {
		return array(
			'valid'   => false,
			'message' => __( 'Senha muito fraca. Use letras maiúsculas, minúsculas, números e caracteres especiais.', 'udi-custom-login' ),
			'score'   => $score,
		);
	}

	return array(
		'valid'   => true,
		'message' => '',
		'score'   => min( $score, 4 ),
	);
}

/**
 * Sanitize redirect URL (allowing only internal URLs).
 *
 * @param string $url         URL to validate.
 * @param string $default_url Default URL if validation fails.
 *
 * @return string
 */
function udi_login_sanitize_redirect( $url, $default_url = '' ) {
	if ( empty( $url ) ) {
		return $default_url;
	}

	$url = esc_url_raw( $url );
	
	// Use WordPress core function to validate redirect
	$validated = wp_validate_redirect( $url, $default_url );
	
	return $validated ? $validated : $default_url;
}

/**
 * Check if user account is locked.
 *
 * @param string $user_login Username or email.
 *
 * @return array {
 *     Lock status.
 *
 *     @type bool   $locked        Whether account is locked.
 *     @type int    $locked_until  Timestamp when lock expires.
 *     @type string $message       User-friendly message.
 * }
 */
function udi_login_check_account_lock( $user_login ) {
	$user = get_user_by( 'login', $user_login );
	if ( ! $user ) {
		$user = get_user_by( 'email', $user_login );
	}

	if ( ! $user ) {
		return array(
			'locked'       => false,
			'locked_until' => 0,
			'message'      => '',
		);
	}

	$locked_until = (int) get_user_meta( $user->ID, '_udi_account_locked_until', true );

	if ( $locked_until && time() < $locked_until ) {
		$minutes = (int) ceil( ( $locked_until - time() ) / 60 );
		return array(
			'locked'       => true,
			'locked_until' => $locked_until,
			'message'      => sprintf(
				/* translators: %d: minutes remaining */
				__( 'Conta temporariamente bloqueada. Tente novamente em %d minutos.', 'udi-custom-login' ),
				max( 1, $minutes )
			),
		);
	}

	// Clear expired lock
	if ( $locked_until ) {
		delete_user_meta( $user->ID, '_udi_account_locked_until' );
	}

	return array(
		'locked'       => false,
		'locked_until' => 0,
		'message'      => '',
	);
}

/**
 * Get security log entries from DB.
 *
 * @param int $limit Number of entries to retrieve.
 *
 * @return array
 */
function udi_login_get_security_logs( $limit = 50 ) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'udi_security_logs';
	
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) !== $table_name ) {
		// Fallback to old option storage
		$logs = get_option( 'udi_login_security_logs', array() );
		return array_slice( $logs, 0, $limit );
	}
	
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d", $limit ),
		ARRAY_A
	);
	
	// Format to match old structure expected by UI
	$formatted = array();
	foreach ( $results as $row ) {
		$formatted[] = array(
			'timestamp'  => $row['created_at'],
			'event_type' => $row['event_type'],
			'message'    => $row['message'],
			'ip'         => $row['ip_address'],
			'context'    => json_decode( $row['meta_json'], true ),
		);
	}
	
	return $formatted;
}

/**
 * Clear security logs.
 *
 * @return bool
 */
function udi_login_clear_security_logs() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'udi_security_logs';
	
	// Legacy clear
	delete_option( 'udi_login_security_logs' );
	
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
		return $wpdb->query( "TRUNCATE TABLE $table_name" );
	}
	
	return true;
}

/**
 * Garbage collector for logs (Retention Policy).
 * Deletes logs older than 30 days.
 *
 * @return void
 */
function udi_login_gc_logs() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'udi_security_logs';
	
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
		$wpdb->query( "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)" );
	}
}
