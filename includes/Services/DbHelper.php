<?php
namespace CustomCssJsRouter\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DbHelper {
	public static function get_css_frontend( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_css_frontend', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_css_frontend', true );
		return ( false === $val ) ? '' : $val;
	}

	public static function get_css_admin( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_css_admin', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_css_admin', true );
		return ( false === $val ) ? '' : $val;
	}

	public static function get_css_both( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_css_both', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_css_both', true );
		return ( false === $val ) ? '' : $val;
	}

	public static function get_js_frontend( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_js_frontend', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_js_frontend', true );
		return ( false === $val ) ? '' : $val;
	}

	public static function get_js_admin( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_js_admin', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_js_admin', true );
		return ( false === $val ) ? '' : $val;
	}

	public static function get_js_both( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_js_both', '' );
		}
		$val = get_post_meta( $page_id, '_ccr_custom_js_both', true );
		return ( false === $val ) ? '' : $val;
	}


	public static function get_css_location( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_css_location', 'header' );
		}
		$location = get_post_meta( $page_id, '_ccr_css_location', true );
		return ( false === $location || '' === $location ) ? 'header' : $location;
	}

	public static function get_js_location( $page_id ) {
		if ( 'global' === $page_id ) {
			return get_option( 'ccr_global_js_location', 'footer' );
		}
		$location = get_post_meta( $page_id, '_ccr_js_location', true );
		return ( false === $location || '' === $location ) ? 'footer' : $location;
	}

	public static function is_enabled( $page_id ) {
		if ( 'global' === $page_id ) {
			return ( 'yes' === get_option( 'ccr_global_enabled', 'no' ) );
		}
		$status = get_post_meta( $page_id, '_ccr_custom_code_enabled', true );
		return ( 'yes' === $status );
	}

	public static function save_css_frontend( $page_id, $css ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_css_frontend', $css );
		} else {
			update_post_meta( $page_id, '_ccr_custom_css_frontend', $css );
		}
	}

	public static function save_css_admin( $page_id, $css ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_css_admin', $css );
		} else {
			update_post_meta( $page_id, '_ccr_custom_css_admin', $css );
		}
	}

	public static function save_css_both( $page_id, $css ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_css_both', $css );
		} else {
			update_post_meta( $page_id, '_ccr_custom_css_both', $css );
		}
	}

	public static function save_js_frontend( $page_id, $js ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_js_frontend', $js );
		} else {
			update_post_meta( $page_id, '_ccr_custom_js_frontend', $js );
		}
	}

	public static function save_js_admin( $page_id, $js ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_js_admin', $js );
		} else {
			update_post_meta( $page_id, '_ccr_custom_js_admin', $js );
		}
	}

	public static function save_js_both( $page_id, $js ) {
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_js_both', $js );
		} else {
			update_post_meta( $page_id, '_ccr_custom_js_both', $js );
		}
	}


	public static function set_enabled( $page_id, $is_enabled ) {
		$status = $is_enabled ? 'yes' : 'no';
		if ( 'global' === $page_id ) {
			update_option( 'ccr_global_enabled', $status );
		} else {
			update_post_meta( $page_id, '_ccr_custom_code_enabled', $status );
		}
	}


}
