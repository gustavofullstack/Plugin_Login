# TriqHub: Custom Login

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759B?logo=wordpress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?logo=woocommerce&logoColor=white)
![License](https://img.shields.io/badge/License-GPLv3-blue)
![Version](https://img.shields.io/badge/Version-1.0.0-green)

## Introduction

**TriqHub: Custom Login** is a professional-grade WordPress plugin that completely replaces all native WordPress and WooCommerce authentication screens with a modern, secure, and highly customizable login experience. Designed for e-commerce and membership sites, it features a distinctive **neon dark theme**, advanced security protocols, and seamless integration with WooCommerce's "My Account" system.

This plugin transforms the standard login, registration, and password recovery flows into a branded, user-friendly interface while significantly enhancing site security through features like rate limiting, reCAPTCHA integration, password strength enforcement, and detailed security logging.

## Features List

### 🎨 **Visual & User Experience**
*   **Complete UI Overhaul:** Replaces `wp-login.php`, registration, and lost password pages with a custom, thematically consistent interface.
*   **Neon Dark Theme:** A modern, visually striking design optimized for user engagement.
*   **Full Customization:** Upload custom logos, background images, and tailor headlines and subheadlines.
*   **WooCommerce Integration:** Applies the custom styling seamlessly to WooCommerce's "My Account" login and registration forms.
*   **Responsive Design:** Ensures a flawless experience across all devices and screen sizes.

### 🔒 **Advanced Security**
*   **Login Attempt Limiting:** Configurable thresholds to lock out IPs after repeated failed attempts.
*   **Google reCAPTCHA v3:** Invisible bot protection for login and registration forms.
*   **Honeypot Anti-Spam:** Invisible form fields to trap automated spam bots.
*   **Password Strength Validation:** Enforces strong passwords with configurable complexity rules and blocks common passwords.
*   **Generic Error Messages:** Obfuscates specific login errors (username vs. password) to prevent user enumeration.
*   **HTTPS Enforcement:** Forces SSL on all login-related pages when configured.
*   **Security Event Logging:** Detailed audit trail of login attempts, failures, lockouts, and suspicious activity (with privacy-conscious IP masking).

### 🔄 **Login with Google (Modern)**
*   **FedCM API Support:** Utilizes Google's latest, privacy-focused Federated Credentials Management API.
*   **One-Tap & Auto-Select:** Enables seamless, one-click sign-in and automatic sign-in for returning users.
*   **Fully Configurable:** Control button theme, size, and behavior directly from the WordPress admin.

### ⚙️ **WooCommerce "My Account" Enhancements**
*   **Custom Endpoint:** Adds a "History" tab to display recently viewed products.
*   **UI Cleanup:** Option to remove the "Downloads" tab for sites not selling digital goods.
*   **Cohesive Styling:** Extends the neon dark theme throughout the customer account area.

### 📊 **Administration & Management**
*   **Centralized Settings:** Intuitive settings page within `Settings > UDI Login`.
*   **Security Log Viewer:** Review and manage security events from the dashboard.
*   **Automatic Updates:** Supports updates via GitHub (including private repositories).
*   **Garbage Collection:** Automated cleanup of old security logs based on a retention policy.

## Quick Start

1.  **Upload & Activate:** Install the plugin via the WordPress admin panel or manually upload to `/wp-content/plugins/`.
2.  **Configure:** Navigate to **Settings > UDI Login** to set up your login page, security options, and visual preferences.
3.  **Create Login Page:** Create a new page and add the `[udi_custom_login]` shortcode. Assign this page in the plugin settings.
4.  **For detailed setup, configuration, and usage instructions, please refer to the comprehensive [User Guide](./docs/USER_GUIDE.md).**

## License

This project is licensed under the **GNU General Public License v3.0**. See the [LICENSE](LICENSE) file for full details.

---

*TriqHub: Custom Login is a product of TriqHub. Built for performance, security, and design.*