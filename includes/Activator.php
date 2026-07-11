<?php
namespace CustomCssJsRouter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {
	public static function activate(): void {
		if ( false === get_option( 'custom_css_js_router_delete_data_on_uninstall' ) ) {
			add_option( 'custom_css_js_router_delete_data_on_uninstall', '1' );
		}
	}
}
