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

	$log_data = array(
		'timestamp'  => current_time( 'mysql' ),
		'event_type' => sanitize_key( $event_type ),
		'message'    => sanitize_text_field( $message ),
		'ip'         => udi_login_get_client_ip(),
		'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'context'    => $context,
	);

	// Store in WordPress option (last 100 events)
	$logs = get_option( 'udi_login_security_logs', array() );
	array_unshift( $logs, $log_data );
	$logs = array_slice( $logs, 0, 100 );
	update_option( 'udi_login_security_logs', $logs, false );

	// Optionally log to error_log for critical events
	if ( in_array( $event_type, array( 'account_locked', 'suspicious_activity' ), true ) ) {
		error_log( sprintf( '[UDI Login Security] %s: %s (IP: %s)', $event_type, $message, $log_data['ip'] ) );
	}

	do_action( 'udi_login_security_event_logged', $event_type, $message, $log_data );
}

/**
 * Get client IP address.
 *
 * @return string
 */
function udi_login_get_client_ip() {
	$ip = '';

	// Prioritize REMOTE_ADDR unless we explicitly trust proxies
	$ip = '';

	if ( defined( 'UDI_TRUST_PROXY' ) && UDI_TRUST_PROXY ) {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		}
	}

	if ( empty( $ip ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	// Handle multiple IPs (comma separated)
	if ( strpos( $ip, ',' ) !== false ) {
		$ip_parts = explode( ',', $ip );
		$ip       = trim( $ip_parts[0] );
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
 * Get security log entries.
 *
 * @param int $limit Number of entries to retrieve.
 *
 * @return array
 */
function udi_login_get_security_logs( $limit = 50 ) {
	$logs = get_option( 'udi_login_security_logs', array() );
	return array_slice( $logs, 0, $limit );
}

/**
 * Clear security logs.
 *
 * @return bool
 */
function udi_login_clear_security_logs() {
	return delete_option( 'udi_login_security_logs' );
}
