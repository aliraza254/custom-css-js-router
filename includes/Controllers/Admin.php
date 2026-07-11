<?php
namespace CustomCssJsRouter\Controllers;

use CustomCssJsRouter\Core\Service;
use CustomCssJsRouter\Services\DbHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin implements Service {
	private string $slug = 'custom-css-js-router';

	public function __construct() {
	}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_settings_page' ], 999 );
		add_action( 'wp_ajax_custom_css_js_router_save_settings', [ $this, 'ajax_save_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_plugins_page_script' ] );
		add_action( 'admin_footer-plugins.php', [ $this, 'admin_footer_plugins_page' ] );
		add_action( 'wp_ajax_ccr_set_uninstall_pref', [ $this, 'ajax_set_uninstall_pref' ] );
	}

	public function register_settings_page(): void {
		$slug = $this->slug;

		// 1. Parent Menu (Renders split-pane editor)
		add_menu_page(
			'Code Router',
			'Code Router',
			'manage_options',
			$slug,
			[ $this, 'render_dashboard_page' ],
			'dashicons-editor-code',
			80
		);

		// 2. Dashboard Submenu
		add_submenu_page(
			$slug,
			__( 'Dashboard - Code Router', 'custom-css-js-router' ),
			__( 'Dashboard', 'custom-css-js-router' ),
			'manage_options',
			$slug,
			[ $this, 'render_dashboard_page' ]
		);

		// 3. How to Use Submenu
		add_submenu_page(
			$slug,
			__( 'How to Use - Code Router', 'custom-css-js-router' ),
			__( 'How to Use', 'custom-css-js-router' ),
			'manage_options',
			$slug . '-docs',
			[ $this, 'render_docs_page' ]
		);
	}



	public function render_dashboard_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'custom-css-js-router' ) );
		}

		$pages = get_pages( [
			'sort_column'  => 'post_title',
			'post_status'  => 'publish,private,draft'
		] );

		$is_safe_mode = \CustomCssJsRouter\CustomCssJsRouter::is_safe_mode_active();
		?>
		<div class="wrap ccr-dashboard-wrap">
			<!-- Header Area -->
			<div class="ccr-header">
				<div class="ccr-header-main">
					<div class="ccr-logo-area">
						<span class="dashicons dashicons-editor-code"></span>
						<div class="ccr-logo-title"><?php esc_html_e( 'Code Router', 'custom-css-js-router' ); ?></div>
					</div>
					<div class="ccr-header-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=custom-css-js-router-docs' ) ); ?>" class="ccr-help-toggle" style="text-decoration: none;">
							<span class="dashicons dashicons-editor-help"></span>
							<?php esc_html_e( 'How to Use', 'custom-css-js-router' ); ?>
						</a>
					</div>
				</div>
			</div>

			<?php if ( $is_safe_mode ) : ?>
			<div class="notice notice-error inline" style="margin: 0 0 20px 0; padding: 10px;">
				<p><strong><?php esc_html_e( '⚠️ SAFE MODE ACTIVE:', 'custom-css-js-router' ); ?></strong> <?php esc_html_e( 'Custom CSS and JS injection is currently bypassed. Fix your broken code here, then disable safe mode to restore normal functionality.', 'custom-css-js-router' ); ?></p>
			</div>
			<?php endif; ?>

			<!-- Main Workspace Grid -->
			<div class="ccr-main-container">
				<!-- Sidebar: List of pages -->
				<div class="ccr-sidebar">
					<div class="ccr-search-box">
						<span class="dashicons dashicons-search"></span>
						<input type="text" id="ccr-page-search" placeholder="<?php esc_attr_e( 'Search pages...', 'custom-css-js-router' ); ?>" />
					</div>
					
					<ul class="ccr-page-list" id="ccr-page-list">
						<!-- Global Settings Item -->
						<?php
						$has_global_css_f = DbHelper::get_css_frontend( 'global' );
						$has_global_css_a = DbHelper::get_css_admin( 'global' );
						$has_global_css_b = DbHelper::get_css_both( 'global' );
						$has_global_js_f  = DbHelper::get_js_frontend( 'global' );
						$has_global_js_a  = DbHelper::get_js_admin( 'global' );
						$has_global_js_b  = DbHelper::get_js_both( 'global' );
						$global_enabled   = DbHelper::is_enabled( 'global' );
						$global_status  = '';
						if ( ! empty( $has_global_css_f ) || ! empty( $has_global_css_a ) || ! empty( $has_global_css_b ) ||
							 ! empty( $has_global_js_f ) || ! empty( $has_global_js_a ) || ! empty( $has_global_js_b ) ) {
							$global_status = $global_enabled ? 'code-active' : 'code-paused';
						}
						?>
						<li class="ccr-page-item ccr-global-item <?php echo esc_attr( $global_status ); ?>" data-page-id="global">
							<div class="ccr-page-info">
								<span class="ccr-page-title"><span class="dashicons dashicons-admin-site-alt3" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span><?php esc_html_e( 'Global Configuration', 'custom-css-js-router' ); ?></span>
								<span class="ccr-page-slug"><?php esc_html_e( 'Applies to all pages', 'custom-css-js-router' ); ?></span>
							</div>
							<div class="ccr-status-dot" title="<?php esc_attr_e( 'Global settings status', 'custom-css-js-router' ); ?>"></div>
						</li>

						<?php if ( ! empty( $pages ) ) : ?>
							<?php 
							foreach ( $pages as $page ) : 
								$has_css_f  = DbHelper::get_css_frontend( $page->ID );
								$has_css_a  = DbHelper::get_css_admin( $page->ID );
								$has_css_b  = DbHelper::get_css_both( $page->ID );
								$has_js_f   = DbHelper::get_js_frontend( $page->ID );
								$has_js_a   = DbHelper::get_js_admin( $page->ID );
								$has_js_b   = DbHelper::get_js_both( $page->ID );
								$is_enabled = DbHelper::is_enabled( $page->ID );
								$status_class = '';
								if ( ! empty( $has_css_f ) || ! empty( $has_css_a ) || ! empty( $has_css_b ) ||
									 ! empty( $has_js_f ) || ! empty( $has_js_a ) || ! empty( $has_js_b ) ) {
									$status_class = $is_enabled ? 'code-active' : 'code-paused';
								}
								?>
								<li class="ccr-page-item <?php echo esc_attr( $status_class ); ?>" data-page-id="<?php echo esc_attr( $page->ID ); ?>">
									<div class="ccr-page-info">
										<span class="ccr-page-title">
											<?php echo esc_html( $page->post_title ); ?>
										</span>
										<span class="ccr-page-slug">/<?php echo esc_html( $page->post_name ); ?></span>
									</div>
									<div class="ccr-status-dot" title="<?php 
										if ( 'code-active' === $status_class ) {
											esc_attr_e( 'Custom CSS/JS is active on this page', 'custom-css-js-router' );
										} elseif ( 'code-paused' === $status_class ) {
											esc_attr_e( 'Custom CSS/JS configured but paused/disabled', 'custom-css-js-router' );
										} else {
											esc_attr_e( 'No custom code configured', 'custom-css-js-router' );
										}
									?>"></div>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li class="ccr-no-pages"><?php esc_html_e( 'No pages found.', 'custom-css-js-router' ); ?></li>
						<?php endif; ?>
					</ul>
				</div>

				<!-- Main Editor Area -->
				<div class="ccr-editor-area" id="ccr-editor-area">
					<div class="ccr-editor-empty-state">
						<div class="empty-state-content">
							<span class="dashicons dashicons-layout"></span>
							<h3><?php esc_html_e( 'No Page Selected', 'custom-css-js-router' ); ?></h3>
							<p><?php esc_html_e( 'Choose a page from the sidebar list to view or edit its custom styles and scripts.', 'custom-css-js-router' ); ?></p>
						</div>
					</div>


					
					<!-- Form Container (Hidden initially) -->
					<div class="ccr-editor-form-wrapper" style="display: none;">
						<div class="ccr-editor-header">
							<div class="editor-header-details">
								<div id="ccr-selected-page-title" class="ccr-selected-page-title"></div>
								<a id="ccr-view-page-link" href="#" target="_blank" class="ccr-view-link">
									<span class="dashicons dashicons-external"></span> <?php esc_html_e( 'View Page', 'custom-css-js-router' ); ?>
								</a>
							</div>
							
							<div class="editor-header-actions">
								<span class="toggle-label"><?php esc_html_e( 'Enable Custom Code', 'custom-css-js-router' ); ?></span>
								<label class="ccr-switch">
									<input type="checkbox" id="ccr-code-toggle">
									<span class="ccr-slider round"></span>
								</label>
							</div>
						</div>

						<!-- Tabs Bar -->
						<div class="ccr-tabs-bar">
							<button type="button" class="ccr-tab-btn active" data-tab="css">
								<span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'CSS Styles', 'custom-css-js-router' ); ?>
							</button>
							<button type="button" class="ccr-tab-btn" data-tab="js">
								<span class="dashicons dashicons-media-code"></span> <?php esc_html_e( 'JavaScript', 'custom-css-js-router' ); ?>
							</button>
						</div>

						<!-- Editor Body -->
						<div class="ccr-editor-body">
							<div class="ccr-code-editor-container">



								<!-- CSS/JS Load Context Selection -->
								<div class="ccr-context-selector-container">
									<span class="ccr-context-label-title"><?php esc_html_e( 'Load Context:', 'custom-css-js-router' ); ?></span>
									<div class="ccr-segmented-control">
										<label>
											<input type="radio" name="ccr-context" value="frontend" checked> <?php esc_html_e( 'Frontend Only', 'custom-css-js-router' ); ?>
										</label>
										<label>
											<input type="radio" name="ccr-context" value="admin"> <?php esc_html_e( 'Admin Dashboard Only', 'custom-css-js-router' ); ?>
										</label>
										<label>
											<input type="radio" name="ccr-context" value="both"> <?php esc_html_e( 'Both (Frontend & Admin)', 'custom-css-js-router' ); ?>
										</label>
									</div>
								</div>





								<textarea id="ccr-custom-editor"></textarea>
							</div>
						</div>

						<!-- Editor Footer -->
						<div class="ccr-editor-footer">
							<button type="button" class="button button-primary button-hero" id="ccr-save-code-btn">
								<span class="spinner-container">
									<span class="ccr-btn-spinner" style="float: none; margin: 0 10px 0 0; display: none;"></span>
								</span>
								<?php esc_html_e( 'Save Changes', 'custom-css-js-router' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Toast Notification -->
		<div id="ccr-toast" class="ccr-toast">
			<span class="ccr-toast-icon dashicons"></span>
			<span class="ccr-toast-message"></span>
		</div>


		<?php
	}



	public function render_docs_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'custom-css-js-router' ) );
		}
		?>
		<div class="wrap ccr-dashboard-wrap">
			<!-- Header Area -->
			<div class="ccr-header">
				<div class="ccr-header-main">
					<div class="ccr-logo-area">
						<span class="dashicons dashicons-editor-help"></span>
						<div class="ccr-logo-title"><?php esc_html_e( 'How to Use - Code Router', 'custom-css-js-router' ); ?></div>
					</div>
					<div class="ccr-header-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=custom-css-js-router' ) ); ?>" class="ccr-help-toggle" style="text-decoration: none;">
							<span class="dashicons dashicons-editor-code"></span>
							<?php esc_html_e( 'Dashboard', 'custom-css-js-router' ); ?>
						</a>
					</div>
				</div>
			</div>

			<div class="ccr-help-content" style="background:#fff; padding:30px; border-radius:12px; box-shadow:var(--ccr-shadow); border:1px solid var(--ccr-border); margin-bottom: 30px;">
				<h3><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Quick Start Guide', 'custom-css-js-router' ); ?></h3>
				<p><?php esc_html_e( 'Code Router allows you to inject custom CSS styles and JavaScript scripts globally or for specific pages without editing your theme files.', 'custom-css-js-router' ); ?></p>
				
				<ul style="margin:20px 0; padding-left:20px; list-style-type:disc;">
					<li>
						<strong><?php esc_html_e( 'Global Configuration:', 'custom-css-js-router' ); ?></strong>
						<?php esc_html_e( 'Applies injected CSS and JS to all pages. Use this for site-wide styling or analytics scripts.', 'custom-css-js-router' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Page-Specific Configuration:', 'custom-css-js-router' ); ?></strong>
						<?php esc_html_e( 'Target individual pages from the sidebar to inject code specifically on that page. Perfect for landing pages or unique layout modifications.', 'custom-css-js-router' ); ?>
					</li>
					<li>
						<strong><?php esc_html_e( 'Load Contexts:', 'custom-css-js-router' ); ?></strong>
						<?php esc_html_e( 'Choose where your CSS and JS will load: Frontend only, Admin Dashboard only, or Both. You can write distinct code blocks for each context concurrently.', 'custom-css-js-router' ); ?>
					</li>
				</ul>

				<hr style="border:0; border-top:1px solid var(--ccr-border); margin:24px 0;">

				<h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Safe Mode Recovery', 'custom-css-js-router' ); ?></h3>
				<p>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: 1: Safe mode query param, 2: Safe mode constant, 3: config file */
							__( 'If you accidentally inject broken code that breaks your site or prevents admin access, you can bypass all custom code execution by adding %1$s to your URL or by defining %2$s in your %3$s file.', 'custom-css-js-router' ),
							'<code>?ccr_safe_mode=1</code>',
							'<code>define( "CCR_SAFE_MODE", true );</code>',
							'<code>wp-config.php</code>'
						),
						[
							'code' => [],
						]
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	public function ajax_save_settings(): void {
		check_ajax_referer( 'custom_css_js_router_save_settings', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'custom-css-js-router' ) ] );
		}

		$delete_data = ( isset( $_POST['delete_data'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['delete_data'] ) ) ) ? '1' : '0';
		update_option( 'custom_css_js_router_delete_data_on_uninstall', $delete_data );

		wp_send_json_success( [ 'message' => __( 'Settings saved successfully!', 'custom-css-js-router' ) ] );
	}

	public function enqueue_plugins_page_script( $hook ): void {
		if ( 'plugins.php' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		
		$nonce = wp_create_nonce( 'ccr_uninstall_pref_nonce' );
		
		$script = "
		jQuery(document).ready(function($) {
			var pendingDeleteUrl = '';
			
			$('input[name=\"ccr_uninstall_mode\"]').on('change', function() {
				$('.ccr-option-label').removeClass('selected');
				$(this).closest('.ccr-option-label').addClass('selected');
				
				var \$btn = $('#ccr-modal-confirm');
				if ($(this).val() === 'delete_all') {
					\$btn.addClass('ccr-delete-action').html('" . esc_js( __( 'Wipe Data & Deactivate', 'custom-css-js-router' ) ) . "');
				} else {
					\$btn.removeClass('ccr-delete-action').html('" . esc_js( __( 'Save & Deactivate', 'custom-css-js-router' ) ) . "');
				}
			});

			$(document).on('click', 'tr[data-slug=\"custom-css-js-router\"] .deactivate a, a[href*=\"action=deactivate\"][href*=\"custom-css-js-router.php\"]', function(e) {
				e.preventDefault();
				pendingDeleteUrl = $(this).attr('href');
				$('#ccr-uninstall-modal-overlay').css('display', 'flex');
			});
			
			$('#ccr-modal-cancel').on('click', function() {
				$('#ccr-uninstall-modal-overlay').hide();
				pendingDeleteUrl = '';
			});
			
			$('#ccr-modal-confirm').on('click', function() {
				var mode = $('input[name=\"ccr_uninstall_mode\"]:checked').val();
				var \$btn = $(this);
				\$btn.text('Processing...').css('opacity', '0.7');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ccr_set_uninstall_pref',
						uninstall_mode: mode,
						nonce: '" . $nonce . "'
					},
					complete: function() {
						window.location.href = pendingDeleteUrl;
					}
				});
			});
		});
		";
		wp_add_inline_script( 'jquery-core', $script );
	}

	public function admin_footer_plugins_page(): void {
		?>
		<style>
			@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
			#ccr-uninstall-modal-overlay {
				position: fixed; top: 0; left: 0; right: 0; bottom: 0;
				background: rgba(15, 23, 42, 0.65); z-index: 999999;
				display: none; align-items: center; justify-content: center;
				backdrop-filter: blur(4px);
			}
			#ccr-uninstall-modal {
				background: #ffffff; color: #0f172a; width: 520px;
				border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15);
				font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
				overflow: hidden; border: 1px solid #e2e8f0;
			}
			.ccr-modal-header {
				padding: 24px 28px; border-bottom: 1px solid #e2e8f0;
				display: flex; align-items: center; gap: 14px; background: #ffffff;
			}
			.ccr-modal-header .dashicons { font-size: 28px; color: #0d9488; width: 28px; height: 28px; }
			.ccr-modal-header h3 { margin: 0; font-size: 19px; color: #0f172a; font-weight: 700; }
			.ccr-modal-body { padding: 28px; background: #f8fafc; }
			.ccr-modal-body p { margin-top: 0; color: #64748b; font-size: 14.5px; line-height: 1.6; margin-bottom: 24px; font-weight: 500; }
			.ccr-option-group { display: flex; flex-direction: column; gap: 14px; }
			.ccr-option-label {
				display: flex; align-items: flex-start; gap: 14px;
				padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px;
				cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
			}
			.ccr-option-label:hover { border-color: #cbd5e1; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
			.ccr-option-label input[type="radio"] { margin-top: 4px; cursor: pointer; accent-color: #0d9488; transform: scale(1.1); }
			.ccr-option-text strong { display: block; color: #0f172a; font-size: 15px; margin-bottom: 6px; font-weight: 600; }
			.ccr-option-text span { display: block; color: #64748b; font-size: 13.5px; line-height: 1.5; }
			.ccr-option-label.selected { border-color: #0d9488; background: #f0fdfa; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15); }
			.ccr-modal-footer {
				padding: 20px 28px; border-top: 1px solid #e2e8f0; background: #ffffff;
				display: flex; justify-content: flex-end; gap: 12px;
			}
			.ccr-modal-btn {
				padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14.5px; font-weight: 600;
				border: none; transition: all 0.2s; text-decoration: none; display: inline-block;
			}
			.ccr-btn-cancel { background: transparent; color: #64748b; border: 2px solid #e2e8f0; }
			.ccr-btn-cancel:hover { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
			.ccr-btn-confirm { background: #0d9488; color: #ffffff; border: 2px solid #0d9488; box-shadow: 0 2px 4px rgba(13, 148, 136, 0.1); }
			.ccr-btn-confirm:hover { background: #0f766e; border-color: #0f766e; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2); }
			.ccr-btn-confirm.ccr-delete-action { background: #ef4444; border-color: #ef4444; }
			.ccr-btn-confirm.ccr-delete-action:hover { background: #dc2626; border-color: #dc2626; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2); }
		</style>
		<div id="ccr-uninstall-modal-overlay">
			<div id="ccr-uninstall-modal">
				<div class="ccr-modal-header">
					<span class="dashicons dashicons-editor-code"></span>
					<h3><?php esc_html_e( 'Deactivating Code Router', 'custom-css-js-router' ); ?></h3>
				</div>
				<div class="ccr-modal-body">
					<p><?php esc_html_e( 'You are about to deactivate the plugin. If you decide to delete it afterwards, how would you like us to handle your saved data?', 'custom-css-js-router' ); ?></p>
					
					<div class="ccr-option-group">
						<label class="ccr-option-label selected">
							<input type="radio" name="ccr_uninstall_mode" value="keep_all" checked>
							<div class="ccr-option-text">
								<strong><?php esc_html_e( 'Keep All Data (Recommended)', 'custom-css-js-router' ); ?></strong>
								<span><?php esc_html_e( 'Leaves all global and page-specific code intact. Safe for reinstalling later.', 'custom-css-js-router' ); ?></span>
							</div>
						</label>
						
						<label class="ccr-option-label">
							<input type="radio" name="ccr_uninstall_mode" value="global_only">
							<div class="ccr-option-text">
								<strong><?php esc_html_e( 'Delete Global Settings Only', 'custom-css-js-router' ); ?></strong>
								<span><?php esc_html_e( 'Deletes site-wide global code upon uninstallation, but leaves individual page-level code intact in the database.', 'custom-css-js-router' ); ?></span>
							</div>
						</label>
						
						<label class="ccr-option-label">
							<input type="radio" name="ccr_uninstall_mode" value="delete_all">
							<div class="ccr-option-text">
								<strong><?php esc_html_e( 'Wipe Everything', 'custom-css-js-router' ); ?></strong>
								<span><?php esc_html_e( 'Permanently destroys all global and page-specific custom code if you delete the plugin.', 'custom-css-js-router' ); ?></span>
							</div>
						</label>
					</div>
				</div>
				<div class="ccr-modal-footer">
					<button type="button" class="ccr-modal-btn ccr-btn-cancel" id="ccr-modal-cancel"><?php esc_html_e( 'Cancel', 'custom-css-js-router' ); ?></button>
					<button type="button" class="ccr-modal-btn ccr-btn-confirm" id="ccr-modal-confirm"><?php esc_html_e( 'Save & Deactivate', 'custom-css-js-router' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	public function ajax_set_uninstall_pref(): void {
		check_ajax_referer( 'ccr_uninstall_pref_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		
		$mode = isset( $_POST['uninstall_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['uninstall_mode'] ) ) : 'keep_all';
		update_option( 'custom_css_js_router_delete_mode', $mode );
		wp_send_json_success();
	}

}

