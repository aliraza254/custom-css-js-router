<?php
/**
 * Uninstall file for Custom CSS JS Router
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Read the deletion mode selected by the user in the popup
// Default to 'keep_all' if not set, preventing accidental data loss
$custom_css_js_router_delete_mode = get_option( 'custom_css_js_router_delete_mode', 'keep_all' );

if ( 'keep_all' !== $custom_css_js_router_delete_mode ) {
	
	// 1. For both 'global_only' and 'delete_all', we delete global options
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'ccr_%' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'custom_css_js_router_%' ) );
	
	// 2. ONLY for 'delete_all', we also delete page-specific postmeta
	if ( 'delete_all' === $custom_css_js_router_delete_mode ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", '\_ccr\_%' ) );
	}
}

// Clean up the temporary preference option itself
delete_option( 'custom_css_js_router_delete_mode' );
// Clean up legacy options
delete_option( 'custom_css_js_router_delete_data_on_uninstall' );
delete_option( 'custom_css_js_router_delete_all_data' );
