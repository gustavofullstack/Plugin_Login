# TriqHub: Custom Login

## Introduction

TriqHub: Custom Login is a comprehensive WordPress plugin that replaces all default WordPress and WooCommerce authentication screens with a fully customizable, secure, and modern login experience. Featuring a distinctive neon dark design aesthetic, the plugin provides advanced security features, extensive customization options, and seamless integration with WooCommerce. It is designed for administrators who require professional-grade authentication interfaces with enhanced security and branding capabilities.

## Features

*   **Complete Login Override:** Redirects all `wp-login.php` and WooCommerce My Account login/registration pages to a custom-designed interface.
*   **Neon Dark Design:** Modern, visually striking login interface with customizable branding elements.
*   **Advanced Security:**
    *   Google reCAPTCHA v3 integration for bot protection.
    *   Configurable login attempt limiting and temporary account lockout.
    *   Optional honeypot anti-spam field.
    *   Security event logging with IP masking and data retention policies.
    *   Strong password validation with configurable complexity rules.
    *   Generic error messages to prevent username enumeration.
*   **WooCommerce Integration:** Seamlessly styles WooCommerce login forms and offers customization options for the My Account area.
*   **Customizable Branding:** Upload custom logos and background images, and configure headlines and subheadlines.
*   **Flexible Redirects:** Set custom redirect URLs for post-login, post-logout, and post-registration actions.
*   **Shortcode & Template Support:** Easily embed the login form anywhere using the `[udi_custom_login]` shortcode or dedicated page templates.
*   **Security Dashboard:** View and manage security logs directly from the WordPress admin.
*   **Automatic Updates:** Supports GitHub-based updates for easy version management.
*   **TriqHub Connector:** Includes integration with the TriqHub ecosystem for extended functionality.

## Installation / Usage

### Installation

1.  **Upload Plugin:** Navigate to **Plugins > Add New** in your WordPress admin. Click **Upload Plugin**, select the `triqhub-custom-login.zip` file, and click **Install Now**.
2.  **Activate Plugin:** After installation, click **Activate Plugin**.
3.  **Initial Setup:** Upon activation, the plugin will create necessary database tables. Configure the plugin via **Settings > UDI Login**.

### Basic Usage

1.  **Create a Login Page:** Create a new page (e.g., "Login") and insert the `[udi_custom_login]` shortcode.
2.  **Configure the Plugin:**
    *   Go to **Settings > UDI Login**.
    *   In the **General Settings** section, select the page you created in the "Página de Login" dropdown.
    *   Enable "Substituir wp-login.php" to redirect the default login page.
3.  **Customize Appearance:** Upload your logo and background image, and set your desired headlines in the **Textos** section.
4.  **(Optional) Configure Security:** Enable and configure reCAPTCHA, login attempt limits, and other security features in the **Segurança** section.

## Configuration / Architecture

### Plugin Structure

The plugin follows a modular architecture:

*   **Main Plugin File (`triqhub-custom-login.php`):** Handles initialization, constants, update checker, and core hooks.
*   **Core Class (`includes/class-udi-login-plugin.php`):** Singleton controller that orchestrates all components.
*   **Components:**
    *   **Renderer (`includes/class-udi-login-renderer.php`):** Manages the rendering of login forms and views.
    *   **Shortcode (`includes/class-udi-login-shortcode.php`):** Handles the `[udi_custom_login]` shortcode.
    *   **Settings (`includes/class-udi-login-settings.php`):** Manages the plugin's admin settings page and options.
    *   **WooCommerce Integration (`includes/class-udi-login-woocommerce.php`):** Overrides WooCommerce templates and adds styling.
    *   **Security Helpers (`includes/security-helpers.php`):** Contains functions for logging, IP handling, password validation, and account locking.
    *   **Helpers (`includes/helpers.php`):** General helper functions for settings retrieval.
*   **Templates (`templates/`):** Contains the HTML/PHP templates for the login interface (`layout.php`) and a dedicated page template (`page-login.php`).
*   **Assets (`assets/`):** Houses CSS and JavaScript files for the frontend and admin styling.

### Key Configuration Options

Settings are managed under **Settings > UDI Login** and stored in the `udi_login_settings` option.

*   **General Settings:**
    *   `enable_custom_login`: Toggle to redirect `wp-login.php`.
    *   `login_page_id`: The ID of the page containing the `[udi_custom_login]` shortcode.
    *   `logo_id` / `background_id`: Media IDs for branding.
    *   `woocommerce_styling`: Apply plugin styling to WooCommerce forms.
*   **Messaging:**
    *   `headline`: Main title on the login card.
    *   `subheadline`: Supporting text.
*   **Security:**
    *   `enable_recaptcha`: Enable Google reCAPTCHA v3.
    *   `recaptcha_site_key` / `recaptcha_secret_key`: Your reCAPTCHA API keys.
    *   `limit_login_enabled`: Enable login attempt limiting.
    *   `limit_login_attempts`: Number of failed attempts before lockout.
    *   `limit_login_lockout`: Lockout duration in minutes.
    *   `enable_security_logging`: Log security events to the database.
    *   `enable_password_strength`: Enforce strong passwords during registration.
    *   `password_min_length` / `password_min_score`: Parameters for password validation.
*   **Redirects:** Configure `redirect_after_login`, `redirect_after_logout`, and `redirect_after_register`.

### Database Schema

The plugin creates a custom table for security logging:
```sql
CREATE TABLE `{wp_prefix}udi_security_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `ip_hash` varchar(64) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT 0,
  `meta_json` longtext,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `ip_hash` (`ip_hash`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
);
```
A daily cron job (`udi_login_daily_gc`) purges records older than 30 days.

## API Reference / Hooks

The plugin provides several actions and filters for developers.

### Actions

*   `udi_login_security_event_logged ( $event_type, $message, $context )`
    *   Fired after a security event is logged to the database.
    *   **Parameters:**
        *   `$event_type` (string): Event type (e.g., 'login_failed', 'account_locked').
        *   `$message` (string): Log message.
        *   `$context` (array): Additional context data (user_id, etc.).

*   `udi_login_intro ( $view, $settings )`
    *   Fired within the introductory section of the login card, before the forms.
    *   **Parameters:**
        *   `$view` (string): Current view ('login', 'register', 'lostpassword').
        *   `$settings` (array): The plugin's settings array.

*   `udi_login_after_card ( $view, $settings )`
    *   Fired after the main login card, outside the `.udi-login-card` container.
    *   **Parameters:** Same as `udi_login_intro`.

*   `udi_login_daily_gc`
    *   Scheduled daily event that triggers the garbage collection for old security logs. Hooked to `udi_login_gc_logs()`.

### Filters

*   `udi_login_validate_password_strength ( array $result )`
    *   Filters the result of the internal password strength validation.
    *   **Parameters:**
        *   `$result` (array): Contains keys `valid` (bool), `message` (string), `score` (int).
    *   **Return:** Modified `$result` array.

*   `udi_login_sanitize_redirect ( string $validated_url, string $original_url, string $default_url )`
    *   Filters the sanitized redirect URL before it is used.
    *   **Parameters:**
        *   `$validated_url` (string): The URL after internal validation.
        *   `$original_url` (string): The originally requested redirect URL.
        *   `$default_url` (string): The fallback URL.
    *   **Return:** The final redirect URL to use.

### Functions

*   `udi_login_log_security_event( $event_type, $message, $context = array() )`
    *   Logs a security event. Respects the `enable_security_logging` setting.
*   `udi_login_get_client_ip()`
    *   Retrieves the client's IP address with support for Cloudflare (`HTTP_CF_CONNECTING_IP`) and opt-in proxy trust (`UDI_TRUST_PROXY` constant).
*   `udi_login_validate_password_strength( $password )`
    *   Validates a password against configured strength rules.
*   `udi_login_check_account_lock( $user_login )`
    *   Checks if a user account is temporarily locked.
*   `udi_login_get_security_logs( $limit = 50 )`
    *   Retrieves security log entries from the database.
*   `udi_login_get_settings()` / `udi_login_get_option( $key, $default = null )`
    *   Retrieve plugin settings.

## Troubleshooting

### Common Issues

1.  **Login page redirects to `wp-login.php` in a loop.**
    *   Ensure the "Substituir wp-login.php" option is enabled.
    *   Verify the "Página de Login" setting points to the correct page containing the `[udi_custom_login]` shortcode.
    *   Check for conflicts with other login redirect plugins (e.g., Members, Theme My Login). Temporarily disable them to test.

2.  **Custom styling is not applied to WooCommerce login forms.**
    *   Confirm the "Estilizar WooCommerce" option is enabled in the settings.
    *   Clear any caching plugins or server-side caches (OPcache, Redis).
    *   Ensure your theme hasn't overridden the WooCommerce template path.

3.  **reCAPTCHA is not showing/working.**
    *   Verify that "Google reCAPTCHA" is enabled.
    *   Ensure both "Site Key" and "Secret Key" are correctly entered. These are different keys.
    *   Check your browser's console for JavaScript errors that might prevent the reCAPTCHA script from loading.

4.  **Security logs are not appearing in the admin.**
    *   Confirm "Registro de Eventos de Segurança" is enabled.
    *   Check that the `{wp_prefix}udi_security_logs` table exists in the database.
    *   Verify the scheduled event `udi_login_daily_gc` is present in "Tools > Scheduled Actions" (if using WP CLI or a plugin to view cron events).

5.  **"Could not create database table" error on activation.**
    *   The database user account used by WordPress may lack `CREATE TABLE` permissions. Contact your hosting provider.
    *   There might be a conflict with an existing table. The plugin should use `dbDelta()` for safe creation, but check for SQL errors in your server logs.

### Debugging

*   Enable `WP_DEBUG` in your `wp-config.php` file to log PHP errors:
    ```php
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    ```
*   For update issues, ensure the `UDI_LOGIN_GITHUB_TOKEN` constant is defined in `wp-config.php` if using a private repository:
    ```php
    define( 'UDI_LOGIN_GITHUB_TOKEN', 'your_github_personal_access_token_here' );
    ```
*   To trust proxy headers (e.g., behind a load balancer), you must explicitly opt-in by defining:
    ```php
    define( 'UDI_TRUST_PROXY', true );
    ```
    **Warning:** Only enable this if you are behind a trusted reverse proxy and have configured it correctly to set the `X-Forwarded-For` header.