<?php
namespace CustomCssJsRouter\Controllers;

use CustomCssJsRouter\Core\Service;
use CustomCssJsRouter\Services\DbHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax implements Service {
	private string $slug = 'custom-css-js-router';

	public function __construct() {
	}

	public function register(): void {
		// Legacy AJAX actions
		add_action( 'wp_ajax_ccr_get_page_code', [ $this, 'ajax_get_page_code' ] );
		add_action( 'wp_ajax_ccr_save_page_code', [ $this, 'ajax_save_page_code' ] );
	}

	public function ajax_get_page_code(): void {
		check_ajax_referer( 'ccr_code_nonce_action', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'custom-css-js-router' ) ) );
		}

		$page_id = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : 0;
		if ( 'global' !== $page_id ) {
			$page_id = absint( $page_id );
		}

		if ( ! $page_id && 'global' !== $page_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid page ID.', 'custom-css-js-router' ) ) );
		}

		$css_frontend = DbHelper::get_css_frontend( $page_id );
		$css_admin    = DbHelper::get_css_admin( $page_id );
		$css_both     = DbHelper::get_css_both( $page_id );
		$js_frontend  = DbHelper::get_js_frontend( $page_id );
		$js_admin     = DbHelper::get_js_admin( $page_id );
		$js_both      = DbHelper::get_js_both( $page_id );
		$css_location = DbHelper::get_css_location( $page_id );
		$js_location  = DbHelper::get_js_location( $page_id );
		$is_enabled   = DbHelper::is_enabled( $page_id );

		$view_url = ( 'global' === $page_id ) ? home_url( '/' ) : get_permalink( $page_id );

		wp_send_json_success( array(
			'css_frontend'    => $css_frontend,
			'css_admin'       => $css_admin,
			'css_both'        => $css_both,
			'js_frontend'     => $js_frontend,
			'js_admin'        => $js_admin,
			'js_both'         => $js_both,
			'css_location'    => $css_location,
			'js_location'     => $js_location,
			'is_enabled'      => $is_enabled,
			'view_url'        => $view_url
		) );
	}

	public function ajax_save_page_code(): void {
		check_ajax_referer( 'ccr_code_nonce_action', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'custom-css-js-router' ) ) );
		}

		$page_id = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : 0;
		if ( 'global' !== $page_id ) {
			$page_id = absint( $page_id );
		}

		if ( ! $page_id && 'global' !== $page_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid page ID.', 'custom-css-js-router' ) ) );
		}

		// Sanitize CSS by stripping all HTML tags to prevent XSS injection.
		$css_frontend = isset( $_POST['css_frontend'] ) ? wp_strip_all_tags( wp_unslash( $_POST['css_frontend'] ) ) : '';
		$css_admin    = isset( $_POST['css_admin'] ) ? wp_strip_all_tags( wp_unslash( $_POST['css_admin'] ) ) : '';
		$css_both     = isset( $_POST['css_both'] ) ? wp_strip_all_tags( wp_unslash( $_POST['css_both'] ) ) : '';

		// For JS: We retain raw scripting content but ensure it is strictly managed and gated to administrator capability.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$js_frontend  = isset( $_POST['js_frontend'] ) ? wp_unslash( $_POST['js_frontend'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$js_admin     = isset( $_POST['js_admin'] ) ? wp_unslash( $_POST['js_admin'] ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$js_both      = isset( $_POST['js_both'] ) ? wp_unslash( $_POST['js_both'] ) : '';
		$is_enabled   = ( isset( $_POST['is_enabled'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['is_enabled'] ) ) );

		DbHelper::save_css_frontend( $page_id, $css_frontend );
		DbHelper::save_css_admin( $page_id, $css_admin );
		DbHelper::save_css_both( $page_id, $css_both );
		DbHelper::save_js_frontend( $page_id, $js_frontend );
		DbHelper::save_js_admin( $page_id, $js_admin );
		DbHelper::save_js_both( $page_id, $js_both );
		DbHelper::set_enabled( $page_id, $is_enabled );

		$status_class = '';
		if ( ! empty( $css_frontend ) || ! empty( $css_admin ) || ! empty( $css_both ) || 
			 ! empty( $js_frontend ) || ! empty( $js_admin ) || ! empty( $js_both ) ) {
			$status_class = $is_enabled ? 'code-active' : 'code-paused';
		}

		wp_send_json_success( array(
			'message'      => __( 'Changes updated successfully.', 'custom-css-js-router' ),
			'status_class' => $status_class
		) );
	}



}

