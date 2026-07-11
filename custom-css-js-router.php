<?php
/**
 * Plugin Name: Custom CSS JS Router
 * Plugin URI:  https://github.com/aliraza254/custom-css-js-router
 * Description: Inject custom CSS, JS, header footer scripts, and tracking codes globally or page-specifically.
 * Version:     1.0.0
 * Author:      Muhammad Ali Raza
 * Author URI:  https://github.com/aliraza254
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-css-js-router
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Constants
define( 'CUSTOM_CSS_JS_ROUTER_VERSION', '1.0.0' );
define( 'CUSTOM_CSS_JS_ROUTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'CUSTOM_CSS_JS_ROUTER_URL', plugin_dir_url( __FILE__ ) );

// Autoloader for Custom CSS JS Router classes (PSR-4)
spl_autoload_register( function ( string $class ) {
	$prefix = 'CustomCssJsRouter\\';
	$base_dir = CUSTOM_CSS_JS_ROUTER_PATH . 'includes/';
	$len    = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Register Activation and Deactivation Hooks
register_activation_hook( __FILE__, function() {
	CustomCssJsRouter\Activator::activate();
} );

register_deactivation_hook( __FILE__, function() {
	CustomCssJsRouter\Deactivator::deactivate();
} );

// Add settings link to plugin action links row
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	if ( class_exists( 'CustomCssJsRouter\Controllers\Admin' ) ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=custom-css-js-router' ) ) . '">' . __( 'Settings', 'custom-css-js-router' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
} );

// Initialize the plugin using OOP orchestrator immediately
if ( class_exists( 'CustomCssJsRouter\CustomCssJsRouter' ) ) {
	$plugin = new CustomCssJsRouter\CustomCssJsRouter();
	$plugin->run();
}
