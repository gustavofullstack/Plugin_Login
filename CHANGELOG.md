# Changelog

All notable changes to this project will be documented in this file.

## [1.0.3] - 2025-12-15

### Security
- **Hardening**: Added `defined('ABSPATH') || exit;` to all template partials.
- **Access Control**: Added `.htaccess` to block access to `composer.json`, `composer.lock`, and `vendor/`.
- **Cleanup**: Removed `test-google-config.php` from production build.
- **Fix**: Removed `emergency-fix.php` and its inclusion logic.

### Changed
- **Refactor**: Moved ad-hoc validation scripts to `includes/Core/Validation.php`.
- **Organization**: Added `.gitignore` to exclude system files and dependencies.

## [1.0.2] - Previous Release
- Initial release with My Account customization.
