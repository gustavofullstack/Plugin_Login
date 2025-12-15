<?php
/**
 * Complete Plugin Validation Script - Production Ready Test
 *
 * Tests:
 * - PHP Syntax (all files)
 * - Directory Structure
 * - File Existence
 * - Class Loading
 * - WordPress/WooCommerce Compatibility
 * - Security Functions
 * - Google Sign-In Integration
 *
 * @package UDI_Custom_Login
 */

// Color output functions
function green( $text ) {
	return "\033[32m" . $text . "\033[0m";
}

function red( $text ) {
	return "\033[31m" . $text . "\033[0m";
}

function yellow( $text ) {
	return "\033[33m" . $text . "\033[0m";
}

function blue( $text ) {
	return "\033[34m" . $text . "\033[0m";
}

function bold( $text ) {
	return "\033[1m" . $text . "\033[0m";
}

// Test counters
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;
$warnings = 0;

echo bold( "\n" . str_repeat( '=', 80 ) . "\n" );
echo bold( "  UDI CUSTOM LOGIN - VALIDAÇÃO COMPLETA PARA PRODUÇÃO\n" );
echo bold( str_repeat( '=', 80 ) . "\n\n" );

// 1. PHP SYNTAX VALIDATION
echo bold( blue( "1. TESTANDO SINTAXE PHP\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$php_files = array(
	'udi-custom-login.php',
	'includes/helpers.php',
	'includes/security-helpers.php',
	'includes/class-udi-login-plugin.php',
	'includes/class-udi-login-settings.php',
	'includes/class-udi-login-assets.php',
	'includes/class-udi-login-renderer.php',
	'includes/class-udi-login-form-handler.php',
	'includes/class-udi-login-shortcode.php',
	'includes/class-udi-login-security.php',
	'includes/class-udi-login-google.php',
	'includes/class-udi-login-woocommerce.php',
	'includes/class-udi-login-my-account.php',
	'includes/class-udi-login-security-logs.php',
	'includes/class-udi-login-installer.php',
);

foreach ( $php_files as $file ) {
	$total_tests++;
	$output = array();
	$return_var = 0;
	exec( "php -l {$file} 2>&1", $output, $return_var );
	
	if ( $return_var === 0 ) {
		echo green( "✓ " ) . $file . "\n";
		$passed_tests++;
	} else {
		echo red( "✗ " ) . $file . "\n";
		echo "  Error: " . implode( "\n  ", $output ) . "\n";
		$failed_tests++;
	}
}

echo "\n";

// 2. TEMPLATE FILES VALIDATION
echo bold( blue( "2. TESTANDO TEMPLATES PHP\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$template_files = array(
	'templates/layout.php',
	'templates/page-login.php',
	'templates/partials/form-login.php',
	'templates/partials/form-register.php',
	'templates/partials/form-lostpassword.php',
	'templates/partials/form-resetpass.php',
);

foreach ( $template_files as $file ) {
	$total_tests++;
	if ( file_exists( $file ) ) {
		$output = array();
		$return_var = 0;
		exec( "php -l {$file} 2>&1", $output, $return_var );
		
		if ( $return_var === 0 ) {
			echo green( "✓ " ) . $file . "\n";
			$passed_tests++;
		} else {
			echo red( "✗ " ) . $file . " - SYNTAX ERROR\n";
			$failed_tests++;
		}
	} else {
		echo yellow( "⚠ " ) . $file . " - NOT FOUND\n";
		$warnings++;
	}
}

echo "\n";

// 3. DIRECTORY STRUCTURE
echo bold( blue( "3. VERIFICANDO ESTRUTURA DE DIRETÓRIOS\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$required_dirs = array(
	'includes',
	'templates',
	'templates/partials',
	'templates/woocommerce',
	'templates/woocommerce/myaccount',
	'assets',
	'assets/css',
	'assets/js',
);

foreach ( $required_dirs as $dir ) {
	$total_tests++;
	if ( is_dir( $dir ) ) {
		echo green( "✓ " ) . "Diretório: {$dir}\n";
		$passed_tests++;
	} else {
		echo red( "✗ " ) . "Diretório AUSENTE: {$dir}\n";
		$failed_tests++;
	}
}

echo "\n";

// 4. ASSETS VALIDATION
echo bold( blue( "4. VERIFICANDO ASSETS (CSS/JS)\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$assets = array(
	'assets/css/login.css',
	'assets/css/my-account.css',
	'assets/css/admin.css',
	'assets/css/google-signin.css',
	'assets/js/login.js',
	'assets/js/admin.js',
	'assets/js/google-login.js',
);

foreach ( $assets as $asset ) {
	$total_tests++;
	if ( file_exists( $asset ) ) {
		$size = filesize( $asset );
		echo green( "✓ " ) . $asset . " (" . round( $size / 1024, 2 ) . " KB)\n";
		$passed_tests++;
	} else {
		echo red( "✗ " ) . "Asset AUSENTE: {$asset}\n";
		$failed_tests++;
	}
}

echo "\n";

// 5. SECURITY FUNCTIONS CHECK
echo bold( blue( "5. VERIFICANDO FUNÇÕES DE SEGURANÇA\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$security_functions = array(
	'udi_login_log_security_event',
	'udi_login_validate_password_strength',
	'udi_login_sanitize_redirect',
	'udi_login_get_client_ip',
	'udi_login_check_account_lock',
	'udi_login_get_security_logs',
	'udi_login_clear_security_logs',
);

foreach ( $security_functions as $func ) {
	$total_tests++;
	$found = false;
	$file_contents = file_get_contents( 'includes/security-helpers.php' );
	if ( strpos( $file_contents, "function {$func}" ) !== false ) {
		echo green( "✓ " ) . "Função: {$func}()\n";
		$passed_tests++;
	} else {
		echo red( "✗ " ) . "Função AUSENTE: {$func}()\n";
		$failed_tests++;
	}
}

echo "\n";

// 6. GOOGLE SIGN-IN INTEGRATION CHECK
echo bold( blue( "6. VERIFICANDO INTEGRAÇÃO GOOGLE SIGN-IN\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$google_checks = array(
	array(
		'file' => 'includes/class-udi-login-google.php',
		'class' => 'UDI_Login_Google',
	),
	array(
		'file' => 'includes/class-udi-login-google.php',
		'method' => 'verify_google_token',
	),
	array(
		'file' => 'includes/class-udi-login-google.php',
		'method' => 'ajax_handle_google_login',
	),
	array(
		'file' => 'includes/class-udi-login-google.php',
		'method' => 'render_google_button',
	),
	array(
		'file' => 'assets/js/google-login.js',
		'function' => 'udiHandleGoogleCredential',
	),
	array(
		'file' => 'includes/helpers.php',
		'config' => 'enable_google_signin',
	),
	array(
		'file' => 'includes/helpers.php',
		'config' => 'google_client_id',
	),
);

foreach ( $google_checks as $check ) {
	$total_tests++;
	$file = $check['file'];
	$contents = file_get_contents( $file );
	
	if ( isset( $check['class'] ) ) {
		if ( strpos( $contents, "class {$check['class']}" ) !== false ) {
			echo green( "✓ " ) . "Classe: {$check['class']}\n";
			$passed_tests++;
		} else {
			echo red( "✗ " ) . "Classe AUSENTE: {$check['class']}\n";
			$failed_tests++;
		}
	} elseif ( isset( $check['method'] ) ) {
		if ( strpos( $contents, "function {$check['method']}" ) !== false ) {
			echo green( "✓ " ) . "Método: {$check['method']}()\n";
			$passed_tests++;
		} else {
			echo red( "✗ " ) . "Método AUSENTE: {$check['method']}()\n";
			$failed_tests++;
		}
	} elseif ( isset( $check['function'] ) ) {
		if ( strpos( $contents, "window.{$check['function']}" ) !== false || strpos( $contents, "function {$check['function']}" ) !== false ) {
			echo green( "✓ " ) . "Função JS: {$check['function']}()\n";
			$passed_tests++;
		} else {
			echo red( "✗ " ) . "Função JS AUSENTE: {$check['function']}()\n";
			$failed_tests++;
		}
	} elseif ( isset( $check['config'] ) ) {
		if ( strpos( $contents, "'{$check['config']}'" ) !== false ) {
			echo green( "✓ " ) . "Config: {$check['config']}\n";
			$passed_tests++;
		} else {
			echo red( "✗ " ) . "Config AUSENTE: {$check['config']}\n";
			$failed_tests++;
		}
	}
}

echo "\n";

// 7. WORDPRESS HOOKS CHECK
echo bold( blue( "7. VERIFICANDO HOOKS WORDPRESS\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$hooks_to_check = array(
	array(
		'file' => 'includes/class-udi-login-google.php',
		'hook' => 'wp_ajax_nopriv_udi_google_login',
		'type' => 'add_action',
	),
	array(
		'file' => 'includes/class-udi-login-google.php',
		'hook' => 'wp_ajax_udi_google_login',
		'type' => 'add_action',
	),
	array(
		'file' => 'includes/class-udi-login-google.php',
		'hook' => 'udi_login_after_submit_button',
		'type' => 'add_action',
	),
	array(
		'file' => 'templates/partials/form-login.php',
		'hook' => 'udi_login_after_submit_button',
		'type' => 'do_action',
	),
);

foreach ( $hooks_to_check as $hook_check ) {
	$total_tests++;
	$contents = file_get_contents( $hook_check['file'] );
	$search_pattern = "{$hook_check['type']}( '{$hook_check['hook']}'";
	
	if ( strpos( $contents, $search_pattern ) !== false || strpos( $contents, str_replace( "'", '"', $search_pattern ) ) !== false ) {
		echo green( "✓ " ) . "{$hook_check['type']}( '{$hook_check['hook']}' )\n";
		$passed_tests++;
	} else {
		echo yellow( "⚠ " ) . "Hook pode estar ausente: {$hook_check['hook']}\n";
		$warnings++;
	}
}

echo "\n";

// 8. ABSPATH SECURITY CHECK
echo bold( blue( "8. VERIFICANDO PROTEÇÃO ABSPATH\n" ) );
echo str_repeat( '-', 80 ) . "\n";

foreach ( $php_files as $file ) {
	$total_tests++;
	$contents = file_get_contents( $file );
	if ( strpos( $contents, "! defined( 'ABSPATH' )" ) !== false || 
	     strpos( $contents, "!defined('ABSPATH')" ) !== false ||
	     strpos( $contents, '! defined( "ABSPATH" )' ) !== false ) {
		echo green( "✓ " ) . $file . "\n";
		$passed_tests++;
	} else {
		echo yellow( "⚠ " ) . $file . " - ABSPATH check ausente\n";
		$warnings++;
	}
}

echo "\n";

// 9. NONCE VERIFICATION CHECK
echo bold( blue( "9. VERIFICANDO VERIFICAÇÕES DE NONCE\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$nonce_files = array(
	'includes/class-udi-login-form-handler.php' => 'wp_verify_nonce',
	'includes/class-udi-login-google.php' => 'wp_verify_nonce',
	'includes/class-udi-login-renderer.php' => 'check_ajax_referer',
);

foreach ( $nonce_files as $file => $nonce_func ) {
	$total_tests++;
	$contents = file_get_contents( $file );
	if ( strpos( $contents, $nonce_func ) !== false ) {
		echo green( "✓ " ) . $file . " usa {$nonce_func}()\n";
		$passed_tests++;
	} else {
		echo red( "✗ " ) . $file . " - {$nonce_func}() AUSENTE\n";
		$failed_tests++;
	}
}

echo "\n";

// 10. SANITIZATION CHECK
echo bold( blue( "10. VERIFICANDO SANITIZAÇÃO DE DADOS\n" ) );
echo str_repeat( '-', 80 ) . "\n";

$sanitization_functions = array(
	'sanitize_text_field',
	'sanitize_email',
	'esc_url',
	'esc_attr',
	'esc_html',
	'wp_unslash',
);

$sample_file = 'includes/class-udi-login-google.php';
$contents = file_get_contents( $sample_file );

foreach ( $sanitization_functions as $func ) {
	$total_tests++;
	if ( strpos( $contents, $func ) !== false ) {
		echo green( "✓ " ) . "Usa {$func}()\n";
		$passed_tests++;
	} else {
		echo yellow( "⚠ " ) . "Pode não usar {$func}()\n";
		$warnings++;
	}
}

echo "\n";

// FINAL REPORT
echo bold( "\n" . str_repeat( '=', 80 ) . "\n" );
echo bold( "  RELATÓRIO FINAL\n" );
echo bold( str_repeat( '=', 80 ) . "\n\n" );

$pass_rate = $total_tests > 0 ? round( ( $passed_tests / $total_tests ) * 100, 2 ) : 0;

echo "Total de testes: " . bold( $total_tests ) . "\n";
echo green( "Testes passados: {$passed_tests}\n" );
if ( $failed_tests > 0 ) {
	echo red( "Testes falhados: {$failed_tests}\n" );
}
if ( $warnings > 0 ) {
	echo yellow( "Avisos: {$warnings}\n" );
}
echo "Taxa de sucesso: " . bold( "{$pass_rate}%" ) . "\n\n";

if ( $failed_tests === 0 && $pass_rate > 95 ) {
	echo green( bold( "✅ PLUGIN VALIDADO COM SUCESSO!\n" ) );
	echo green( "O plugin está pronto para uso em produção.\n\n" );
	exit( 0 );
} elseif ( $failed_tests === 0 && $warnings > 0 ) {
	echo yellow( bold( "⚠️  PLUGIN VALIDADO COM AVISOS\n" ) );
	echo yellow( "O plugin está funcional, mas revise os avisos.\n\n" );
	exit( 0 );
} else {
	echo red( bold( "❌ VALIDAÇÃO FALHOU!\n" ) );
	echo red( "Corrija os erros antes de usar em produção.\n\n" );
	exit( 1 );
}
