<?php
namespace CustomCssJsRouter;

use CustomCssJsRouter\Core\Container;
use CustomCssJsRouter\Core\Plugin;
use CustomCssJsRouter\Services\AssetManager;
use CustomCssJsRouter\Services\CodeInjector;
use CustomCssJsRouter\Controllers\Admin;
use CustomCssJsRouter\Controllers\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CustomCssJsRouter {
	private static Container $container;

	public function __construct() {
		$this->define_constants();
		$this->bootstrap();
	}

	private function define_constants(): void {
		if ( ! defined( 'CUSTOM_CSS_JS_ROUTER_VERSION' ) ) {
			define( 'CUSTOM_CSS_JS_ROUTER_VERSION', '1.0.0' );
		}
	}

	private function bootstrap(): void {
		self::$container = new Container();

		// 1. Bind Services
		self::$container->singleton( 'assets', function( $c ) {
			return new AssetManager();
		} );

		self::$container->singleton( 'injector', function( $c ) {
			return new CodeInjector();
		} );

		// 2. Bind Controllers
		self::$container->singleton( 'admin', function( $c ) {
			return new Admin();
		} );

		self::$container->singleton( 'ajax', function( $c ) {
			return new Ajax();
		} );
	}

	public static function get_container(): Container {
		return self::$container;
	}

	public static function is_safe_mode_active(): bool {
		if ( defined( 'CCR_SAFE_MODE' ) && CCR_SAFE_MODE ) {
			return true;
		}

		$has_param = false;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['ccr_safe_mode'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['ccr_safe_mode'] ) ) ) {
			$has_param = true;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_POST['ccr_safe_mode'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['ccr_safe_mode'] ) ) ) {
			$has_param = true;
		} elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			if ( strpos( $request_uri, 'ccr_safe_mode=1' ) !== false ) {
				$has_param = true;
			}
		} elseif ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			if ( strpos( $referer, 'ccr_safe_mode=1' ) !== false ) {
				$has_param = true;
			}
		}

		if ( $has_param ) {
			if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
				return true;
			}
		}
		return false;
	}

	public function run(): void {
		$plugin = new Plugin( self::$container );
		$plugin->boot();
	}
}

