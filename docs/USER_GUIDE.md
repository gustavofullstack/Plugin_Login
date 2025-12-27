# TriqHub: Custom Login – User Guide

## Table of Contents
1. [Overview](#overview)
2. [System Requirements](#system-requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Use Cases](#use-cases)
6. [Troubleshooting & FAQ](#troubleshooting--faq)
7. [Changelog](#changelog)

---

## Overview

**TriqHub: Custom Login** is a premium WordPress plugin that completely replaces all WordPress and WooCommerce authentication screens with a custom-designed neon dark experience. It provides enhanced security features, advanced configuration options, and seamless integration with modern authentication methods including Google Sign-In with FedCM support.

### Key Features
- **Complete Authentication Overhaul**: Replaces wp-login.php, WooCommerce login, and registration forms
- **Neon Dark Design**: Modern, visually striking interface with customizable branding
- **Enhanced Security**: Rate limiting, reCAPTCHA, password strength validation, security logging
- **Google Sign-In**: Modern FedCM API support with One Tap and auto-select options
- **WooCommerce Integration**: Customized My Account page with additional endpoints
- **Privacy-Focused**: IP masking, GDPR-compliant logging, secure redirect handling
- **Automatic Updates**: GitHub integration for seamless updates

---

## System Requirements

### Minimum Requirements
- **WordPress**: 5.6 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher / MariaDB 10.1 or higher
- **Memory Limit**: 128MB minimum (256MB recommended)
- **SSL Certificate**: Required for production environments

### Recommended Environment
- **PHP**: 8.0 or higher
- **WordPress**: 6.0 or higher
- **WooCommerce**: 6.0 or higher (optional, for full integration)
- **cURL**: Enabled for Google Sign-In and reCAPTCHA
- **HTTPS**: Enabled site-wide

### Compatibility Notes
- **Multisite**: Partially supported (requires network activation)
- **Caching Plugins**: Compatible with most (WP Rocket, W3 Total Cache)
- **Security Plugins**: May require whitelisting for custom login paths
- **Translation Ready**: Full .pot file included for localization

---

## Installation

### Method 1: WordPress Admin (Standard)
1. Navigate to **Plugins → Add New**
2. Click **Upload Plugin**
3. Select the `triqhub-custom-login.zip` file
4. Click **Install Now**
5. Activate the plugin

### Method 2: Manual Installation via FTP/SFTP
1. Download the plugin ZIP file
2. Extract the contents to your local machine
3. Upload the `triqhub-custom-login` folder to `/wp-content/plugins/`
4. Navigate to **Plugins** in WordPress admin
5. Locate **TriqHub: Custom Login** and click **Activate**

### Method 3: Composer Installation
```bash
composer require gustavofullstack/triqhub-custom-login
```

### Post-Installation Steps
1. **Verify Activation**: Check that the plugin appears in the active plugins list
2. **Check Requirements**: Navigate to **Settings → UDI Login** to verify system compatibility
3. **Create Login Page**: Create a new page with the `[udi_custom_login]` shortcode
4. **Configure Settings**: Complete the initial setup in the plugin settings

### Update Configuration
For private GitHub repositories, add to `wp-config.php`:
```php
define('UDI_LOGIN_GITHUB_TOKEN', 'your_github_personal_access_token');
```

---

## Configuration

### 1. General Settings

#### Enable Custom Login
- **Purpose**: Redirects all wp-login.php attempts to your custom page
- **Default**: Enabled
- **Security Impact**: Hides default WordPress login URL, reducing brute-force attacks
- **Note**: Keep disabled during initial setup to maintain access

#### Login Page Selection
- **Purpose**: Specifies which page contains the `[udi_custom_login]` shortcode
- **Configuration**: Use the dropdown to select an existing page
- **Best Practice**: Create a dedicated page (e.g., `/login/`) with only the shortcode
- **SEO Consideration**: Add `noindex` meta tag to prevent search engine indexing

#### Branding Assets
- **Logo**: Media ID for the login screen logo (recommended: 300×100px, PNG with transparency)
- **Background Image**: Optional full-screen background (recommended: 1920×1080px, optimized)
- **File Format**: Supports JPG, PNG, WebP, SVG
- **Optimization**: Use WordPress media compression or external tools like TinyPNG

#### WooCommerce Styling
- **Purpose**: Applies neon dark theme to WooCommerce My Account forms
- **Default**: Enabled
- **Compatibility**: Works with most WooCommerce-compatible themes
- **Custom CSS**: Additional styling can be added via `udi_login_custom_css` filter

### 2. Messaging & Content

#### Headline
- **Purpose**: Primary welcome message above login form
- **Character Limit**: Recommended ≤ 60 characters
- **Dynamic Variables**: None supported (static text only)
- **Example**: "Welcome Back" or "Access Your Account"

#### Subheadline
- **Purpose**: Secondary descriptive text
- **Character Limit**: Recommended ≤ 160 characters
- **Formatting**: Supports basic HTML (`<strong>`, `<em>`, `<a>`)
- **Example**: "Sign in to access exclusive content and manage your profile"

#### Social Login Note
- **Purpose**: Instructional text above social login buttons
- **Formatting**: Full HTML support with automatic paragraph wrapping
- **Best Practice**: Keep concise with clear CTAs
- **Example**: "Or sign in quickly with your social account"

### 3. Security Configuration

#### Google reCAPTCHA v3
- **Purpose**: Invisible bot protection without user interaction
- **Setup**:
  1. Register at [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin)
  2. Choose "reCAPTCHA v3"
  3. Add your domain(s)
  4. Copy Site Key and Secret Key
  5. Paste into plugin settings
- **Score Threshold**: Configured server-side (default: 0.5)
- **Privacy Note**: Complies with Google's Terms of Service

#### Honeypot Anti-Spam
- **Purpose**: Invisible field that traps automated bots
- **Implementation**: CSS-hidden field validated server-side
- **Effectiveness**: Blocks 85-95% of basic spam bots
- **Compatibility**: Works alongside reCAPTCHA for layered protection

#### Generic Error Messages
- **Purpose**: Obfuscates specific login failure reasons
- **Security Benefit**: Prevents username enumeration attacks
- **Message Display**: All failures show "Invalid credentials"
- **Debug Mode**: Disable for troubleshooting login issues

#### Rate Limiting
- **Attempts Allowed**: Number of failed attempts before lockout (default: 5)
- **Lockout Duration**: Minutes account remains locked (default: 15)
- **IP-Based**: Tracks by hashed IP address for privacy
- **Reset**: Automatically clears after lockout period expires

#### Security Logging
- **Purpose**: Audit trail of authentication events
- **Events Logged**:
  - Failed login attempts
  - Successful logins
  - Account lockouts
  - Suspicious activities
- **Retention**: 30 days automatic cleanup
- **Privacy**: IP addresses are masked (e.g., 192.168.1.xxx)
- **Storage**: Dedicated database table `wp_udi_security_logs`

#### Password Strength Validation
- **Minimum Length**: Character count requirement (default: 8)
- **Score System**: 0-4 scale based on complexity
- **Score Requirements**:
  - 0: Very Weak (blocked)
  - 1: Weak (minimum default)
  - 2: Medium
  - 3: Strong
  - 4: Very Strong
- **Common Passwords**: Blocks 100+ known weak passwords
- **Complexity Rules**: Requires mix of uppercase, lowercase, numbers, symbols

### 4. Google Sign-In Configuration

#### Client ID Setup
1. **Create Project**: [Google Cloud Console](https://console.cloud.google.com)
2. **Enable APIs**: "Google Identity Services" API
3. **Configure OAuth Consent Screen**:
   - User Type: External
   - App Name: Your Site Name
   - Support Email: Your contact email
   - Scopes: `email`, `profile`, `openid`
4. **Create Credentials**: OAuth 2.0 Client ID (Web application)
5. **Authorized JavaScript Origins**: `https://yourdomain.com`
6. **Authorized Redirect URIs**: `https://yourdomain.com/wp-json/udi-login/v1/google/callback`

#### FedCM (Modern API)
- **Purpose**: Cookie-less authentication for Chrome 117+
- **Benefits**: Better privacy, faster loading, future-proof
- **Requirements**: HTTPS, correct CORS headers
- **Fallback**: Automatically uses traditional OAuth if unsupported

#### One Tap & Auto-Select
- **One Tap**: Floating prompt for returning users
- **Auto-Select**: Automatic sign-in for recognized accounts
- **UX Considerations**: May increase conversion but reduce explicit consent
- **GDPR Compliance**: Ensure cookie consent banners accommodate these features

#### Button Customization
- **Theme Options**:
  - `outline`: White with border (light backgrounds)
  - `filled_blue`: Google's standard blue
  - `filled_black`: Dark theme compatible
- **Size Options**: Small (150px), Medium (200px), Large (250px)
- **Text Options**: "Sign in with Google", "Sign up with Google", "Continue with Google"

### 5. Redirect Configuration

#### Post-Login Redirect
- **Priority Order**:
  1. `redirect_to` parameter in URL
  2. Plugin setting
  3. WooCommerce dashboard
  4. WordPress admin
- **Security**: Validates internal URLs only
- **WooCommerce**: Respects `wc_get_account_endpoint_url()`

#### Post-Registration Redirect
- **Default**: Same as login redirect
- **Customization**: Separate setting for onboarding flows
- **WooCommerce**: Can redirect to checkout or specific product

#### Post-Logout Redirect
- **Options**: Homepage, custom URL, or previous page
- **Security**: Prevents redirect loops
- **Cache Consideration**: Ensure cached pages don't show authenticated content

### 6. WooCommerce My Account Customization

#### Visual Customization
- **Neon Dark Theme**: Applied to all My Account sections
- **Compatibility**: Tested with Storefront, Flatsome, Astra
- **Custom CSS Hook**: `udi_login_woocommerce_css`

#### History Endpoint
- **Purpose**: Adds "History" tab with recently viewed products
- **Data Source**: WordPress transients (14-day expiration)
- **Privacy**: User-specific, not shared
- **Label Customization**: Fully translatable

#### Downloads Removal
- **Purpose**: Hides Downloads tab for non-digital stores
- **Conditional**: Only removes if no digital products exist
- **Fallback**: Reappears automatically if digital products added

### 7. Advanced Configuration

#### Database Schema
```sql
CREATE TABLE wp_udi_security_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    message VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    ip_hash VARCHAR(64) NOT NULL,
    user_id BIGINT UNSIGNED DEFAULT 0,
    meta_json LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_ip_hash (ip_hash),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### WP-CLI Commands
```bash
# Clear security logs
wp udi-login clear-logs

# Export security logs
wp udi-login export-logs --format=json --file=logs.json

# Reset user lockouts
wp udi-login reset-lockouts --user=username

# Test reCAPTCHA configuration
wp udi-login test-recaptcha
```

#### Hooks & Filters
```php
// Modify login form HTML
add_filter('udi_login_form_html', function($html, $view) {
    return $html;
}, 10, 2);

// Add custom validation
add_action('udi_login_validate', function($data, $errors) {
    if (empty($data['custom_field'])) {
        $errors->add('custom_field', 'Required field');
    }
}, 10, 2);

// Modify redirect URLs
add_filter('udi_login_redirect_url', function($url, $context) {
    if ('register' === $context) {
        return 'https://example.com/welcome';
    }
    return $url;
}, 10, 2);

// Custom security logging
add_action('udi_login_security_event_logged', function($event_type, $message, $context) {
    // Integrate with external SIEM
}, 10, 3);
```

#### Constants for wp-config.php
```php
// Trust proxy headers (behind load balancer)
define('UDI_TRUST_PROXY', true);

// GitHub token for private updates
define('UDI_LOGIN_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxxxxxxxxxx');

// Disable specific features
define('UDI_LOGIN_DISABLE_RECAPTCHA', false);
define('UDI_LOGIN_DISABLE_GOOGLE_SIGNIN', false);

// Custom database table prefix
define('UDI_LOGIN_DB_PREFIX', 'custom_');
```

---

## Use Cases

### 1. E-commerce Website with WooCommerce
**Scenario**: Online store needing branded login, enhanced security, and customer history tracking.

**Configuration**:
- Enable WooCommerce styling
- Activate History endpoint
- Set post-login redirect to account dashboard
- Enable reCAPTCHA and rate limiting
- Configure Google Sign-In for faster checkout

**Result**: Cohesive brand experience from login through purchase, reduced cart abandonment, improved security.

### 2. Membership Site with Premium Content
**Scenario**: Subscription-based site requiring secure access and multiple authentication methods.

**Configuration**:
- Custom login page with premium branding
- Strong password requirements
- Security logging enabled
- Google Sign-In with One Tap
- Redirect to content library after login

**Result**: Reduced support tickets for password resets, increased member retention, audit trail for compliance.

### 3. Corporate Intranet with Strict Security
**Scenario**: Internal portal requiring maximum security and compliance logging.

**Configuration**:
- Generic error messages enabled
- Password strength score 4 (very strong)
- 3 failed attempt lockout
- Daily security log review
- HTTPS enforcement
- No social login

**Result**: Meets corporate security policies, prevents credential stuffing, detailed audit capabilities.

### 4. Multisite Network with Shared Login
**Scenario**: WordPress network needing centralized authentication with per-site branding.

**Configuration**:
- Network activate plugin
- Per-site logo and background settings
- Centralized security logging
- Shared Google Sign-In configuration
- Site-specific redirects

**Result**: Consistent security policies, reduced management overhead, flexible branding.

### 5. Development/Staging Environment
**Scenario**: Testing environment needing isolation from production.

**Configuration**:
- Disable custom login during development
- Use `UDI_LOGIN_DISABLE_RECAPTCHA` constant
- Local-only IP ranges for testing
- Debug mode for error messages
- No connection to live Google credentials

**Result**: Safe testing without affecting production security or user experience.

---

## Troubleshooting & FAQ

### Installation Issues

#### "Plugin could not be activated because it triggered a fatal error."
**Cause**: PHP version incompatibility or missing dependencies.
**Solution**:
1. Check PHP version ≥ 7.4
2. Verify Composer dependencies are installed
3. Check error logs: `wp-content/debug.log`
4. Temporarily disable other plugins to identify conflicts

#### "The package could not be installed. PCLZIP_ERR_BAD_FORMAT (-10)"
**Cause**: Corrupted ZIP file or server timeout.
**Solution**:
1. Re-download the plugin
2. Increase PHP `max_execution_time` to 300
3. Install manually via FTP
4. Check server disk space

#### "Update failed: Download failed. Unauthorized"
**Cause**: Missing GitHub token for private repository.
**Solution**:
```php
// Add to wp-config.php
define('UDI_LOGIN_GITHUB_TOKEN', 'your_token_here');
```
Generate token: GitHub → Settings → Developer Settings → Personal Access Tokens → repo scope.

### Configuration Problems

#### Custom Login Page Not Working
**Symptoms**: Still seeing wp-login.php or redirect loops.
**Debug Steps**:
1. Verify page contains `[udi_custom_login]` shortcode
2. Check page is published and accessible
3. Clear all caching (plugin, server, CDN)
4. Test with default WordPress theme
5. Check `.htaccess` for conflicting rules

#### Google Sign-In Not Appearing
**Checklist**:
- [ ] Google Sign-In enabled in settings
- [ ] Valid Client ID entered
- [ ] HTTPS enabled on site
- [ ] Domain added to Authorized Origins
- [ ] No JavaScript errors in console
- [ ] Not blocked by ad-blockers

**Debug Command**:
```javascript
// Browser console
console.log(window.google);
// Should show Google API object
```

#### reCAPTCHA Not Working
**Common Issues**:
1. **Keys Mismatched**: Ensure v3 keys (not v2)
2. **Domain Not Authorized**: Add all domains (www/non-www)
3. **Score Too High**: Default threshold may block legitimate users
4. **Caching**: reCAPTCHA token may be cached

**Test Endpoint**:
```
POST /wp-json/udi-login/v1/verify-recaptcha
Content-Type: application/json

{"token": "recaptcha_token_here"}
```

#### WooCommerce Styling Not Applying
