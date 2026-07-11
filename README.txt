=== Custom CSS JS Router ===
Contributors: aliraza254
Tags: custom css, custom js, header footer, code injector, scripts manager
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily inject custom CSS, JS, header footer scripts, tracking codes, and pixel codes globally or page-specifically.

== Description ==
Custom CSS JS Router is a lightweight utility that allows you to quickly and easily insert custom CSS styles, JavaScript scripts, and HTML tracking codes (such as Google Analytics, Facebook Pixel, Google Tag Manager, and custom meta tags) into your WordPress website.

Whether you need to add custom header and footer scripts globally across the entire site, or target specific posts and pages individually, Custom CSS JS Router handles it efficiently without bloating your site. You can also specify different load contexts (Frontend Only, Admin Only, or Both).

= Features =
* **Global Injection:** Easily load custom code globally across your frontend site, admin dashboard, or both.
* **Page-Specific Injection:** Target individual pages and posts to load custom CSS/JS only where needed, reducing page weight and load times.
* **Safe Mode Recovery:** Built-in safe mode protection prevents site lockout. If you write broken code, append `?ccr_safe_mode=1` to any admin URL to bypass code execution.
* **Modern Interface:** Sleek, user-friendly control dashboard with CodeMirror syntax highlighting.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/custom-css-js-router/` directory, or install the plugin directly through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the 'Code Router' menu link in the WordPress admin menu to configure your custom scripts and styles.

== Frequently Asked Questions ==

= Does this plugin support custom PHP code execution? =
No. In compliance with WordPress.org security guidelines regarding dynamic code execution, this plugin only supports CSS and JavaScript injection.

= Can I use this site-wide? =
Yes. Select the 'Global Configuration' item from the sidebar to apply styles and scripts to all pages.

= What happens if I write broken code? =
If your code breaks the site, append `?ccr_safe_mode=1` to any admin URL, or add `define( 'CCR_SAFE_MODE', true );` in your `wp-config.php` file to disable code output and safely edit it.

== Screenshots ==
1. The main dashboard editor interface with the sidebar page navigator.

== Upgrade Notice ==

= 1.0.0 =
* Initial public release with full WordPress.org compliance.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Cleaned up licensing and transitioned to a fully free, compliant version.
* Added native page-specific CSS/JS routing.
