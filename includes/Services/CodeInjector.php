<?php
namespace CustomCssJsRouter\Services;

use CustomCssJsRouter\Core\Service;
use CustomCssJsRouter\Services\DbHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CodeInjector implements Service {
	public function register(): void {
		add_action( 'wp_head', [ $this, 'inject_custom_css' ], 999 );
		add_action( 'wp_footer', [ $this, 'inject_custom_js' ], 999 );
		add_action( 'admin_head', [ $this, 'inject_custom_css_admin' ], 999 );
		add_action( 'admin_footer', [ $this, 'inject_custom_js_admin' ], 999 );
	}

	private function is_safe_mode_active(): bool {
		if ( class_exists( 'CustomCssJsRouter\CustomCssJsRouter' ) && method_exists( 'CustomCssJsRouter\CustomCssJsRouter', 'is_safe_mode_active' ) ) {
			return \CustomCssJsRouter\CustomCssJsRouter::is_safe_mode_active();
		}
		
		if ( defined( 'CCR_SAFE_MODE' ) && CCR_SAFE_MODE ) {
			return true;
		}
		return false;
	}

	public function inject_custom_css(): void {
		if ( $this->is_safe_mode_active() ) {
			return;
		}

		$output = '';

		// Global CSS
		if ( DbHelper::is_enabled( 'global' ) ) {
			$css_frontend = DbHelper::get_css_frontend( 'global' );
			$css_both     = DbHelper::get_css_both( 'global' );

			if ( ! empty( trim( $css_frontend ) ) ) {
				$output .= "\n<!-- Custom Global CSS (Frontend Only) -->\n";
				$output .= "<style id=\"ccr-global-custom-css-frontend\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_frontend ) . "\n";
				$output .= "</style>\n<!-- End Custom Global CSS (Frontend Only) -->\n";
			}
			if ( ! empty( trim( $css_both ) ) ) {
				$output .= "\n<!-- Custom Global CSS (Both) -->\n";
				$output .= "<style id=\"ccr-global-custom-css-both\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_both ) . "\n";
				$output .= "</style>\n<!-- End Custom Global CSS (Both) -->\n";
			}
		}

		// Page-Specific CSS
		if ( is_singular() ) {
			$page_id = get_the_ID();
			if ( $page_id && DbHelper::is_enabled( $page_id ) ) {
				$css_frontend = DbHelper::get_css_frontend( $page_id );
				$css_both     = DbHelper::get_css_both( $page_id );

				if ( ! empty( trim( $css_frontend ) ) ) {
					$output .= "\n<!-- Custom Page CSS (Frontend Only) -->\n";
					$output .= "<style id=\"ccr-page-custom-css-frontend\" type=\"text/css\">\n";
					$output .= wp_strip_all_tags( $css_frontend ) . "\n";
					$output .= "</style>\n<!-- End Custom Page CSS (Frontend Only) -->\n";
				}
				if ( ! empty( trim( $css_both ) ) ) {
					$output .= "\n<!-- Custom Page CSS (Both) -->\n";
					$output .= "<style id=\"ccr-page-custom-css-both\" type=\"text/css\">\n";
					$output .= wp_strip_all_tags( $css_both ) . "\n";
					$output .= "</style>\n<!-- End Custom Page CSS (Both) -->\n";
				}
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

	public function inject_custom_css_admin(): void {
		if ( $this->is_safe_mode_active() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'custom-css-js-router' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		$output = '';

		// Global CSS
		if ( DbHelper::is_enabled( 'global' ) ) {
			$css_admin = DbHelper::get_css_admin( 'global' );
			$css_both  = DbHelper::get_css_both( 'global' );

			if ( ! empty( trim( $css_admin ) ) ) {
				$output .= "\n<!-- Custom Global CSS (Admin Only) -->\n";
				$output .= "<style id=\"ccr-global-custom-css-admin\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_admin ) . "\n";
				$output .= "</style>\n<!-- End Custom Global CSS (Admin Only) -->\n";
			}
			if ( ! empty( trim( $css_both ) ) ) {
				$output .= "\n<!-- Custom Global CSS (Both) -->\n";
				$output .= "<style id=\"ccr-global-custom-css-both-admin\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_both ) . "\n";
				$output .= "</style>\n<!-- End Custom Global CSS (Both) -->\n";
			}
		}

		$post_id = 0;
		global $post;
		if ( isset( $post->ID ) ) {
			$post_id = absint( $post->ID );
		}

		if ( $post_id && DbHelper::is_enabled( $post_id ) ) {
			$css_admin = DbHelper::get_css_admin( $post_id );
			$css_both  = DbHelper::get_css_both( $post_id );

			if ( ! empty( trim( $css_admin ) ) ) {
				$output .= "\n<!-- Custom Page CSS (Admin Only) -->\n";
				$output .= "<style id=\"ccr-page-custom-css-admin\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_admin ) . "\n";
				$output .= "</style>\n<!-- End Custom Page CSS (Admin Only) -->\n";
			}
			if ( ! empty( trim( $css_both ) ) ) {
				$output .= "\n<!-- Custom Page CSS (Both) -->\n";
				$output .= "<style id=\"ccr-page-custom-css-both-admin\" type=\"text/css\">\n";
				$output .= wp_strip_all_tags( $css_both ) . "\n";
				$output .= "</style>\n<!-- End Custom Page CSS (Both) -->\n";
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

	public function inject_custom_js(): void {
		if ( $this->is_safe_mode_active() ) {
			return;
		}

		$output = '';

		// Global JS
		if ( DbHelper::is_enabled( 'global' ) ) {
			$js_frontend = DbHelper::get_js_frontend( 'global' );
			$js_both     = DbHelper::get_js_both( 'global' );

			if ( ! empty( trim( $js_frontend ) ) ) {
				$output .= "\n<!-- Custom Global JS (Frontend Only) -->\n";
				$output .= "<script id=\"ccr-global-custom-js-frontend\" type=\"text/javascript\">\n";
				$output .= $js_frontend . "\n";
				$output .= "</script>\n<!-- End Custom Global JS (Frontend Only) -->\n";
			}
			if ( ! empty( trim( $js_both ) ) ) {
				$output .= "\n<!-- Custom Global JS (Both) -->\n";
				$output .= "<script id=\"ccr-global-custom-js-both\" type=\"text/javascript\">\n";
				$output .= $js_both . "\n";
				$output .= "</script>\n<!-- End Custom Global JS (Both) -->\n";
			}
		}

		// Page-Specific JS
		if ( is_singular() ) {
			$page_id = get_the_ID();
			if ( $page_id && DbHelper::is_enabled( $page_id ) ) {
				$js_frontend = DbHelper::get_js_frontend( $page_id );
				$js_both     = DbHelper::get_js_both( $page_id );

				if ( ! empty( trim( $js_frontend ) ) ) {
					$output .= "\n<!-- Custom Page JS (Frontend Only) -->\n";
					$output .= "<script id=\"ccr-page-custom-js-frontend\" type=\"text/javascript\">\n";
					$output .= $js_frontend . "\n";
					$output .= "</script>\n<!-- End Custom Page JS (Frontend Only) -->\n";
				}
				if ( ! empty( trim( $js_both ) ) ) {
					$output .= "\n<!-- Custom Page JS (Both) -->\n";
					$output .= "<script id=\"ccr-page-custom-js-both\" type=\"text/javascript\">\n";
					$output .= $js_both . "\n";
					$output .= "</script>\n<!-- End Custom Page JS (Both) -->\n";
				}
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

	public function inject_custom_js_admin(): void {
		if ( $this->is_safe_mode_active() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'custom-css-js-router' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}

		$output = '';

		// Global JS
		if ( DbHelper::is_enabled( 'global' ) ) {
			$js_admin = DbHelper::get_js_admin( 'global' );
			$js_both  = DbHelper::get_js_both( 'global' );

			if ( ! empty( trim( $js_admin ) ) ) {
				$output .= "\n<!-- Custom Global JS (Admin Only) -->\n";
				$output .= "<script id=\"ccr-global-custom-js-admin\" type=\"text/javascript\">\n";
				$output .= $js_admin . "\n";
				$output .= "</script>\n<!-- End Custom Global JS (Admin Only) -->\n";
			}
			if ( ! empty( trim( $js_both ) ) ) {
				$output .= "\n<!-- Custom Global JS (Both) -->\n";
				$output .= "<script id=\"ccr-global-custom-js-both-admin\" type=\"text/javascript\">\n";
				$output .= $js_both . "\n";
				$output .= "</script>\n<!-- End Custom Global JS (Both) -->\n";
			}
		}

		// Page-Specific JS (Admin Only / Both)
		$post_id = 0;
		global $post;
		if ( isset( $post->ID ) ) {
			$post_id = absint( $post->ID );
		}

		if ( $post_id && DbHelper::is_enabled( $post_id ) ) {
			$js_admin = DbHelper::get_js_admin( $post_id );
			$js_both  = DbHelper::get_js_both( $post_id );

			if ( ! empty( trim( $js_admin ) ) ) {
				$output .= "\n<!-- Custom Page JS (Admin Only) -->\n";
				$output .= "<script id=\"ccr-page-custom-js-admin\" type=\"text/javascript\">\n";
				$output .= $js_admin . "\n";
				$output .= "</script>\n<!-- End Custom Page JS (Admin Only) -->\n";
			}
			if ( ! empty( trim( $js_both ) ) ) {
				$output .= "\n<!-- Custom Page JS (Both) -->\n";
				$output .= "<script id=\"ccr-page-custom-js-both-admin\" type=\"text/javascript\">\n";
				$output .= $js_both . "\n";
				$output .= "</script>\n<!-- End Custom Page JS (Both) -->\n";
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
	}

}
