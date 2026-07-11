<?php
namespace CustomCssJsRouter\Services;

use CustomCssJsRouter\Core\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AssetManager implements Service {
	private string $slug = 'custom-css-js-router';
	public function __construct() {
	}

	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, $this->slug ) === false ) {
			return;
		}



		// 2. Enqueue settings dashboard assets on Dashboard, Hooks and Docs pages
		wp_enqueue_style(
			$this->slug . '-admin-settings-css',
			CUSTOM_CSS_JS_ROUTER_URL . 'admin/css/admin-settings.css',
			[],
			CUSTOM_CSS_JS_ROUTER_VERSION
		);

		// Only enqueue CodeMirror and Javascript on Dashboard & Hooks pages
		if ( strpos( $hook, $this->slug . '-license' ) === false && strpos( $hook, $this->slug . '-settings' ) === false ) {
			$css_editor_settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
			$js_editor_settings  = wp_enqueue_code_editor( [ 'type' => 'text/javascript' ] );

			wp_enqueue_script(
				$this->slug . '-admin-settings-js',
				CUSTOM_CSS_JS_ROUTER_URL . 'admin/js/admin-settings.js',
				[ 'jquery', 'code-editor' ],
				CUSTOM_CSS_JS_ROUTER_VERSION,
				true
			);

			wp_localize_script(
				$this->slug . '-admin-settings-js',
				'ccrData',
				[
					'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
					'nonce'             => wp_create_nonce( 'ccr_code_nonce_action' ),
					'cssEditorSettings' => $css_editor_settings,
					'jsEditorSettings'  => $js_editor_settings,
					'isSafeMode'        => \CustomCssJsRouter\CustomCssJsRouter::is_safe_mode_active(),
					'strings'           => [
						'selectPage'  => __( 'Select a page from the sidebar to customize its CSS and JS.', 'custom-css-js-router' ),
						'loading'     => __( 'Loading editor...', 'custom-css-js-router' ),
						'saveSuccess' => __( 'Changes saved successfully!', 'custom-css-js-router' ),
						'saveError'   => __( 'Failed to save changes. Please try again.', 'custom-css-js-router' ),
						'loadError'   => __( 'Failed to load custom code. Please reload the page.', 'custom-css-js-router' ),
					]
				]
			);
		}
	}
}

