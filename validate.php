#!/usr/bin/env php
<?php
/**
 * Script de validação e teste do plugin UDI Custom Login.
 *
 * Este script verifica:
 * - Sintaxe PHP de todos os arquivos
 * - Estrutura de diretórios
 * - Existência de funcionalidades críticas
 * - Verificação de segurança básica
 *
 * @package UDI_Custom_Login
 */

echo "\n";
echo "==========================================\n";
echo "  UDI Custom Login - Script de Validação \n";
echo "==========================================\n\n";

$plugin_dir = __DIR__;
$errors = array();
$warnings = array();
$passed = 0;
$total = 0;

/**
 * Test PHP syntax.
 */
function test_php_syntax( $file ) {
	$output = array();
	$return_var = null;
	exec( "php -l " . escapeshellarg( $file ) . " 2>&1", $output, $return_var );
	return 0 === $return_var;
}

/**
 * Check if file exists.
 */
function check_file( $file, &$errors, &$passed, &$total ) {
	$total++;
	if ( file_exists( $file ) ) {
		echo "✓ Arquivo existe: " . basename( $file ) . "\n";
		$passed++;
		return true;
	} else {
		$errors[] = "✗ Arquivo não encontrado: $file";
		return false;
	}
}

/**
 * Test PHP file.
 */
function test_php_file( $file, &$errors, &$warnings, &$passed, &$total ) {
	$total++;
	if ( ! file_exists( $file ) ) {
		$errors[] = "✗ Arquivo não encontrado: $file";
		return false;
	}

	if ( test_php_syntax( $file ) ) {
		echo "✓ Sintaxe PHP válida: " . basename( $file ) . "\n";
		$passed++;
		
		// Check for common security issues
		$content = file_get_contents( $file );
		
		// Check ABSPATH check
		if ( ! preg_match( '/if\s*\(\s*!\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)\s*\)/', $content ) ) {
			$warnings[] = "⚠ Falta verificação ABSPATH em: " . basename( $file );
		}
		
		return true;
	} else {
		$errors[] = "✗ Erro de sintaxe PHP em: $file";
		return false;
	}
}

echo "1. Verificando estrutura de diretórios...\n";
echo "-------------------------------------------\n";

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
	$full_path = $plugin_dir . '/' . $dir;
	$total++;
	if ( is_dir( $full_path ) ) {
		echo "✓ Diretório existe: $dir\n";
		$passed++;
	} else {
		$errors[] = "✗ Diretório não encontrado: $dir";
	}
}

echo "\n2. Verificando arquivos principais...\n";
echo "-------------------------------------------\n";

$main_files = array(
	'udi-custom-login.php',
	'README.md',
);

foreach ( $main_files as $file ) {
	check_file( $plugin_dir . '/' . $file, $errors, $passed, $total );
}

echo "\n3. Testando sintaxe de classes PHP...\n";
echo "-------------------------------------------\n";

$php_classes = array(
	'includes/helpers.php',
	'includes/security-helpers.php',
	'includes/class-udi-login-plugin.php',
	'includes/class-udi-login-settings.php',
	'includes/class-udi-login-assets.php',
	'includes/class-udi-login-renderer.php',
	'includes/class-udi-login-form-handler.php',
	'includes/class-udi-login-shortcode.php',
	'includes/class-udi-login-security.php',
	'includes/class-udi-login-woocommerce.php',
	'includes/class-udi-login-my-account.php',
	'includes/class-udi-login-security-logs.php',
	'includes/class-udi-login-installer.php',
);

foreach ( $php_classes as $file ) {
	test_php_file( $plugin_dir . '/' . $file, $errors, $warnings, $passed, $total );
}

echo "\n4. Verificando assets (CSS/JS)...\n";
echo "-------------------------------------------\n";

$assets = array(
	'assets/js/login.js',
	'assets/js/admin.js',
);

foreach ( $assets as $file ) {
	check_file( $plugin_dir . '/' . $file, $errors, $passed, $total );
}

echo "\n5. Verificando templates...\n";
echo "-------------------------------------------\n";

$templates = array(
	'templates/layout.php',
	'templates/page-login.php',
	'templates/partials/form-login.php',
	'templates/partials/form-register.php',
	'templates/partials/form-lostpassword.php',
	'templates/partials/form-resetpass.php',
);

foreach ( $templates as $file ) {
	$full_path = $plugin_dir . '/' . $file;
	$total++;
	if ( file_exists( $full_path ) ) {
		if ( test_php_syntax( $full_path ) ) {
			echo "✓ Template válido: " . basename( $file ) . "\n";
			$passed++;
		} else {
			$errors[] = "✗ Erro de sintaxe em template: $file";
		}
	} else {
		$warnings[] = "⚠ Template não encontrado: $file";
	}
}

echo "\n6. Verificando funcionalidades de segurança...\n";
echo "-------------------------------------------\n";

// Check if security helpers exist
$security_functions = array(
	'udi_login_log_security_event',
	'udi_login_validate_password_strength',
	'udi_login_sanitize_redirect',
	'udi_login_get_client_ip',
);

$helpers_content = file_get_contents( $plugin_dir . '/includes/security-helpers.php' );

foreach ( $security_functions as $func ) {
	$total++;
	if ( strpos( $helpers_content, "function $func" ) !== false ) {
		echo "✓ Função de segurança encontrada: $func()\n";
		$passed++;
	} else {
		$errors[] = "✗ Função de segurança não encontrada: $func()";
	}
}

// Results
echo "\n==========================================\n";
echo "  RESULTADOS\n";
echo "==========================================\n\n";

$percentage = $total > 0 ? round( ( $passed / $total ) * 100, 1 ) : 0;

echo "Total de testes: $total\n";
echo "Testes passados: $passed (" . $percentage . "%)\n";
echo "Erros encontrados: " . count( $errors ) . "\n";
echo "Avisos: " . count( $warnings ) . "\n\n";

if ( ! empty( $errors ) ) {
	echo "ERROS:\n";
	echo "------\n";
	foreach ( $errors as $error ) {
		echo "$error\n";
	}
	echo "\n";
}

if ( ! empty( $warnings ) ) {
	echo "AVISOS:\n";
	echo "-------\n";
	foreach ( $warnings as $warning ) {
		echo "$warning\n";
	}
	echo "\n";
}

if ( empty( $errors ) && $percentage >= 90 ) {
	echo "✅ PLUGIN VALIDADO COM SUCESSO!\n";
	echo "O plugin está pronto para uso em produção.\n\n";
	exit( 0 );
} elseif ( empty( $errors ) ) {
	echo "⚠️  PLUGIN PARCIALMENTE VALIDADO\n";
	echo "Existem alguns avisos, mas nenhum erro crítico.\n\n";
	exit( 0 );
} else {
	echo "❌ VALIDAÇÃO FALHOU\n";
	echo "Corrija os erros antes de usar o plugin.\n\n";
	exit( 1 );
}
