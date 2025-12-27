# TriqHub Custom Login – API Reference

## Overview

This document provides comprehensive technical reference for the TriqHub Custom Login plugin, detailing all public functions, hooks, filters, classes, and API endpoints available for developers.

---

## 1. Core Constants

| Constant | Value | Description | Scope |
|----------|-------|-------------|-------|
| `UDI_LOGIN_VERSION` | `'1.0.0'` | Current plugin version | Global |
| `UDI_LOGIN_PLUGIN_FILE` | `__FILE__` | Absolute path to main plugin file | Global |
| `UDI_LOGIN_PLUGIN_DIR` | `plugin_dir_path(__FILE__)` | Plugin directory path (with trailing slash) | Global |
| `UDI_LOGIN_PLUGIN_URL` | `plugin_dir_url(__FILE__)` | Plugin URL (with trailing slash) | Global |
| `UDI_TRUST_PROXY` | `boolean` | **Optional:** When defined as `true`, enables trust for `HTTP_X_FORWARDED_FOR` header | wp-config.php |

---

## 2. Helper Functions

### 2.1 Settings & Configuration

#### `udi_login_option_name()`
Returns the option name used for plugin settings storage.

**Parameters:** None  
**Return:** `string` – Option name (`'udi_login_settings'`)  
**Example:**
```php
$option_name = udi_login_option_name(); // Returns 'udi_login_settings'
```

#### `udi_login_get_settings()`
Retrieves all plugin settings with defaults applied.

**Parameters:** None  
**Return:** `array` – Complete settings array with merged defaults  
**Default Settings Structure:**
```php
[
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
    'enable_security_logging'  => false,
    'enable_password_strength' => false,
    'password_min_length'      => 8,
    'password_min_score'       => 2,
    'enable_google_signin'     => true,
    'google_client_id'         => '380224261904-8vl2tsvutfe0gtccg93aspi0ha4rtn5l.apps.googleusercontent.com',
    'google_enable_fedcm'      => true,
    'google_enable_onetap'     => true,
    'google_enable_auto_select' => false,
    'google_button_type'       => 'standard',
    'google_button_theme'      => 'filled_blue',
    'google_button_size'       => 'large',
    'google_button_text'       => 'signin_with',
]
```

#### `udi_login_get_option($key, $default = null)`
Retrieves a specific setting value.

**Parameters:**
- `$key` (string) – Setting key to retrieve
- `$default` (mixed) – Default value if key doesn't exist

**Return:** `mixed` – Setting value or default  
**Example:**
```php
$logo_id = udi_login_get_option('logo_id', 0);
$enable_recaptcha = udi_login_get_option('enable_recaptcha', false);
```

#### `udi_login_array_get($array, $key, $default = null)`
Safe array value retrieval with null coalescing.

**Parameters:**
- `$array` (array) – Source array
- `$key` (string) – Array key
- `$default` (mixed) – Default value

**Return:** `mixed` – Array value or default  
**Example:**
```php
$value = udi_login_array_get($data, 'user_id', 0);
```

---

### 2.2 Security Functions

#### `udi_login_log_security_event($event_type, $message, $context = array())`
Logs security events to the database with privacy-aware IP handling.

**Parameters:**
- `$event_type` (string) – Event category: `login_failed`, `login_success`, `account_locked`, `suspicious_activity`
- `$message` (string) – Human-readable description
- `$context` (array) – Additional metadata (user_id, username, etc.)

**Return:** `void`  
**Database Schema:**
```sql
CREATE TABLE wp_udi_security_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,  -- Masked IP
    ip_hash VARCHAR(64) NOT NULL,     -- HMAC-SHA256 of real IP
    user_id BIGINT UNSIGNED DEFAULT 0,
    meta_json LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_ip_hash (ip_hash),
    INDEX idx_created_at (created_at)
);
```

**Example:**
```php
udi_login_log_security_event(
    'login_failed',
    'Invalid credentials for username: admin',
    [
        'username' => 'admin',
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]
);
```

#### `udi_login_get_client_ip()`
Retrieves client IP address with Cloudflare and proxy support.

**Return:** `string` – Valid IP address or `'0.0.0.0'`  
**IP Resolution Priority:**
1. `HTTP_CF_CONNECTING_IP` (Cloudflare)
2. `HTTP_X_FORWARDED_FOR` (Only if `UDI_TRUST_PROXY` is `true`)
3. `REMOTE_ADDR` (Fallback)

**Example:**
```php
$ip = udi_login_get_client_ip(); // Returns '192.168.1.100'
```

#### `udi_login_validate_password_strength($password)`
Validates password strength based on configurable criteria.

**Parameters:**
- `$password` (string) – Password to validate

**Return:** `array` – Validation result  
**Return Structure:**
```php
[
    'valid'   => bool,    // Whether password meets requirements
    'message' => string,  // Error message if invalid
    'score'   => int,     // Strength score 0-4
]
```

**Scoring Algorithm:**
| Condition | Points |
|-----------|--------|
| Length ≥ 8 | +1 |
| Length ≥ 12 | +1 |
| Contains both lowercase and uppercase | +1 |
| Contains numbers | +1 |
| Contains special characters | +1 |

**Example:**
```php
$result = udi_login_validate_password_strength('Weak123');
// Returns: ['valid' => false, 'message' => 'Senha muito fraca...', 'score' => 3]
```

#### `udi_login_sanitize_redirect($url, $default_url = '')`
Sanitizes redirect URLs, allowing only internal WordPress URLs.

**Parameters:**
- `$url` (string) – URL to validate
- `$default_url` (string) – Fallback URL

**Return:** `string` – Validated URL or default  
**Uses:** WordPress Core `wp_validate_redirect()`  
**Example:**
```php
$safe_url = udi_login_sanitize_redirect(
    'https://external.com',
    home_url('/my-account/')
);
// Returns: home_url('/my-account/') if external.com is not allowed
```

#### `udi_login_check_account_lock($user_login)`
Checks if a user account is temporarily locked due to failed attempts.

**Parameters:**
- `$user_login` (string) – Username or email

**Return:** `array` – Lock status  
**Return Structure:**
```php
[
    'locked'       => bool,    // Whether account is currently locked
    'locked_until' => int,     // Unix timestamp when lock expires
    'message'      => string,  // User-friendly lock message
]
```

**Storage:** Uses `_udi_account_locked_until` user meta  
**Example:**
```php
$lock_status = udi_login_check_account_lock('user@example.com');
if ($lock_status['locked']) {
    echo $lock_status['message']; // "Conta temporariamente bloqueada..."
}
```

#### `udi_login_get_security_logs($limit = 50)`
Retrieves security log entries from database.

**Parameters:**
- `$limit` (int) – Maximum number of entries to retrieve

**Return:** `array` – Log entries sorted by most recent  
**Return Structure:**
```php
[
    [
        'timestamp'  => '2024-01-15 14:30:00',
        'event_type' => 'login_failed',
        'message'    => 'Invalid credentials',
        'ip'         => '192.168.1.xxx',
        'context'    => ['username' => 'admin']
    ],
    // ...
]
```

#### `udi_login_clear_security_logs()`
Clears all security logs from database and legacy option storage.

**Return:** `bool` – Success status  
**Example:**
```php
$success = udi_login_clear_security_logs();
```

#### `udi_login_gc_logs()`
**Internal Function:** Garbage collector for logs (deletes entries older than 30 days).  
**Trigger:** Daily via WordPress cron (`udi_login_daily_gc`)

---

## 3. WordPress Hooks & Filters

### 3.1 Actions

| Hook | Description | Parameters | Priority | Source |
|------|-------------|------------|----------|--------|
| `udi_login_daily_gc` | Daily garbage collection for security logs | None | Default | `register_activation_hook()` |
| `udi_login_security_event_logged` | Fires after a security event is logged | 1. `$event_type` (string)<br>2. `$message` (string)<br>3. `$context` (array) | Default | `udi_login_log_security_event()` |
| `udi_login_intro` | Renders content in the login card intro section | 1. `$view` (string)<br>2. `$settings` (array) | Default | `templates/layout.php` |
| `udi_login_after_card` | Renders content after the main login card | 1. `$view` (string)<br>2. `$settings` (array) | Default | `templates/layout.php` |
| `wp_login_failed` | **WordPress Core:** Enhanced by plugin to record failed attempts | 1. `$username` (string) | 10 | `UDI_Login_Security::record_failed_login()` |
| `wp_login` | **WordPress Core:** Clears login attempt counters | 1. `$user_login` (string)<br>2. `$user` (WP_User) | 10 | `UDI_Login_Security::clear_login_attempts()` |
| `wp_logout` | **WordPress Core:** Handles custom logout redirects | None | 20 | `UDI_Login_Security::redirect_after_logout()` |
| `admin_enqueue_scripts` | **WordPress Core:** Enqueues TriqHub admin styles | 1. `$hook_suffix` (string) | Default | `triqhub_enqueue_admin_Plugin_Login()` |

### 3.2 Filters

| Filter | Description | Parameters | Return | Source |
|--------|-------------|------------|--------|--------|
| `login_errors` | Obfuscates login error messages for security | 1. `$error` (string) | `string` | `UDI_Login_Security::obfuscate_login_errors()` |
| `authenticate` | Blocks login attempts based on rate limiting | 1. `$user` (mixed)<br>2. `$username` (string)<br>3. `$password` (string) | `WP_User\|WP_Error\|null` | `UDI_Login_Security::maybe_block_login()` |
| `woocommerce_locate_template` | Overrides WooCommerce login template | 1. `$template` (string)<br>2. `$template_name` (string)<br>3. `$template_path` (string) | `string` | `UDI_Login_WooCommerce::override_login_template()` |
| `plugin_action_links_{plugin}` | Adds settings link to plugins page | 1. `$links` (array) | `array` | `UDI_Login_Settings::action_links()` |

---

## 4. Classes & Public Methods

### 4.1 `UDI_Login_Plugin` (Main Controller)

**Singleton Pattern:** Accessed via `UDI_Login_Plugin::instance()`

**Public Methods:**
| Method | Description | Parameters | Return |
|--------|-------------|------------|--------|
| `instance()` | Gets singleton instance | None | `UDI_Login_Plugin` |
| `get_renderer()` | Gets template renderer instance | None | `UDI_Login_Renderer` |
| `get_settings()` | Gets settings controller instance | None | `UDI_Login_Settings` |

### 4.2 `UDI_Login_Renderer`

**Responsibility:** Handles template rendering and view logic

**Public Methods:**
| Method | Description | Parameters | Return |
|--------|-------------|------------|--------|
| `render($view, $data = [])` | Renders a specific view | 1. `$view` (string): 'login', 'register', 'lostpassword'<br>2. `$data` (array): Template data | `string` HTML |
| `get_form_html($view)` | Gets form HTML for a view | `$view` (string) | `string` HTML |
| `is_login_context()` | Checks if current request is login-related | None | `bool` |

### 4.3 `UDI_Login_Shortcode`

**Shortcode:** `[udi_custom_login]`

**Attributes:**
| Attribute | Description | Default | Values |
|-----------|-------------|---------|--------|
| `view` | Initial form view to display | Current `udi_action` parameter | `login`, `register`, `lostpassword` |

**Usage Examples:**
```php
// Basic usage
echo do_shortcode('[udi_custom_login]');

// Force registration view
echo do_shortcode('[udi_custom_login view="register"]');

// URL parameter override
// ?udi_action=register shows registration form
```

### 4.4 `UDI_Login_Settings`

**Settings Page:** `wp-admin/options-general.php?page=udi-login-settings`

**Option Name:** `udi_login_settings`

**Public Methods:**
| Method | Description | Parameters | Return |
|--------|-------------|------------|--------|
| `register_menu()` | Registers admin menu | None | `void` |
| `register_settings()` | Registers settings fields | None | `void` |
| `sanitize($input)` | Sanitizes settings input | `$input` (array) | `array` |
| `render_page()` | Renders settings page | None | `void` |

**Settings Fields Reference:**

| Section | Field | Type | Description |
|---------|-------|------|-------------|
| General | `enable_custom_login` | Toggle | Redirect wp-login.php to custom page |
| General | `login_page_id` | Page Selector | Page containing `[udi_custom_login]` shortcode |
| General | `logo_id` | Media ID | Logo image media ID |
| General | `background_id` | Media ID | Background image media ID |
| General | `woocommerce_styling` | Toggle | Apply styling to WooCommerce forms |
| Messaging | `headline` | Text | Main headline text |
| Messaging | `subheadline` | Textarea | Secondary message |
| Security | `enable_recaptcha` | Toggle | Enable Google reCAPTCHA |
| Security | `recaptcha_site_key` | Text | reCAPTCHA Site Key |
| Security | `recaptcha_secret_key` | Text | reCAPTCHA Secret Key |
| Security | `limit_login_enabled` | Toggle | Enable login attempt limiting |
| Security | `limit_login_attempts` | Number | Max failed attempts before lock |
| Security | `limit_login_lockout` | Number | Lockout duration (minutes) |
| Security | `enable_security_logging` | Toggle | Log security events to database |
| Security | `enable_password_strength` | Toggle | Enforce strong passwords |
| Security | `password_min_length` | Number | Minimum password length |
| Security | `password_min_score` | Number | Minimum strength score (0-4) |
| Google Sign-In | `enable_google_signin` | Toggle | Enable Google Sign-In |
| Google Sign-In | `google_client_id` | Text | Google OAuth Client ID |
| Google Sign-In | `google_enable_fedcm` | Toggle | Enable FedCM API |
| Google Sign-In | `google_enable_onetap` | Toggle | Enable One Tap prompt |
| Google Sign-In | `google_enable_auto_select` | Toggle | Enable auto-login for returning users |
| Google Sign-In | `google_button_theme` | Select | Button theme