/**
 * Custom CSS & JS Router - Admin Settings JS
 */
jQuery(document).ready(function($) {
	var activePageId = null;
	var cmInstance = null;
	var isSaving = false;
	var activeTab = 'css'; // 'css' or 'js'

	var activeContext = 'frontend'; // 'frontend', 'admin', or 'both'

	// Local cache of code for the currently selected page
	var localCssFrontend = '';
	var localCssAdmin = '';
	var localCssBoth = '';
	var localJsFrontend = '';
	var localJsAdmin = '';
	var localJsBoth = '';




	function getUrlParameter(name) {
		name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
		var results = regex.exec(location.search);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	}

	function updateUrlParams(pageId, tab) {
		var currentUrl = new URL(window.location.href);
		if (pageId) {
			currentUrl.searchParams.set('ccr_page', pageId);
		} else {
			currentUrl.searchParams.delete('ccr_page');
		}
		if (tab) {
			currentUrl.searchParams.set('ccr_tab', tab);
		} else {
			currentUrl.searchParams.delete('ccr_tab');
		}
		window.history.replaceState({}, '', currentUrl.toString());
	}

	function getCachedValue(tab, context) {
		if (tab === 'css') {
			if (context === 'frontend') return localCssFrontend || '';
			if (context === 'admin') return localCssAdmin || '';
			if (context === 'both') return localCssBoth || '';
		} else if (tab === 'js') {
			if (context === 'frontend') return localJsFrontend || '';
			if (context === 'admin') return localJsAdmin || '';
			if (context === 'both') return localJsBoth || '';
		}
		return '';
	}

	function setCachedValue(tab, context, val) {
		if (tab === 'css') {
			if (context === 'frontend') localCssFrontend = val;
			else if (context === 'admin') localCssAdmin = val;
			else if (context === 'both') localCssBoth = val;
		} else if (tab === 'js') {
			if (context === 'frontend') localJsFrontend = val;
			else if (context === 'admin') localJsAdmin = val;
			else if (context === 'both') localJsBoth = val;
		}
	}

	// DOM Elements
	var $emptyState = $('.ccr-editor-empty-state');
	var $formWrapper = $('.ccr-editor-form-wrapper');
	var $pageTitle = $('#ccr-selected-page-title');
	var $viewPageLink = $('#ccr-view-page-link');
	var $codeToggle = $('#ccr-code-toggle');
	var $saveBtn = $('#ccr-save-code-btn');
	var $btnSpinner = $('.ccr-btn-spinner');
	var $toast = $('#ccr-toast');
	var $helpToggle = $('#ccr-help-toggle');
	var $helpDrawer = $('#ccr-help-drawer');

	var $contextContainer = $('.ccr-context-selector-container');
	var $codeEditorContainer = $('.ccr-code-editor-container');

	/**
	 * Help Drawer Toggle
	 */
	$helpToggle.on('click', function() {
		$(this).toggleClass('active');
		$helpDrawer.slideToggle(200);
	});

	function getCodeMirror() {
		if (!cmInstance && typeof wp !== 'undefined' && wp.codeEditor) {
			// Use the CSS settings as our base, since we start on the CSS tab.
			var settings = ccrData.cssEditorSettings || {};
			
			if (settings.codemirror) {
				settings.codemirror.lineWrapping = true;
				settings.codemirror.styleActiveLine = true;
				settings.codemirror.matchBrackets = true;
				// Disable linting because we dynamically switch modes between CSS and JS,
				// which confuses the static linter configuration.
				settings.codemirror.lint = false;

				// Bind Shift-Tab to smart auto-indent formatting
				settings.codemirror.extraKeys = settings.codemirror.extraKeys || {};
				settings.codemirror.extraKeys["Shift-Tab"] = function(cm) {
					cm.operation(function() {
						if (cm.somethingSelected()) {
							var from = cm.getCursor("start").line;
							var to = cm.getCursor("end").line;
							for (var i = from; i <= to; i++) {
								cm.indentLine(i, "smart");
							}
						} else {
							var cur = cm.getCursor();
							cm.indentLine(cur.line, "smart");
						}
					});
				};
			}
			var editor = wp.codeEditor.initialize('ccr-custom-editor', settings);
			cmInstance = editor.codemirror;
		}
		return cmInstance;
	}

	/**
	 * Show Toast Notification.
	 */
	function showToast(message, type) {
		$toast.removeClass('success error show');
		$toast.find('.ccr-toast-message').text(message);
		
		var iconClass = type === 'success' ? 'dashicons-saved' : 'dashicons-warning';
		$toast.find('.ccr-toast-icon').attr('class', 'ccr-toast-icon dashicons ' + iconClass);
		
		$toast.addClass(type + ' show');

		// Hide after 3.5 seconds.
		setTimeout(function() {
			$toast.removeClass('show');
		}, 3500);
	}

	/**
	 * Populate PHP Hook Selector Dropdown.
	 */
	function updatePhpSelector(pageId) {
		var $hiddenInput = $('#ccr-php-hook');
		var $optionsContainer = $('#ccr-php-hook-options');
		$optionsContainer.empty();
		
		var hooks = [];
		if (pageId === 'global') {
			hooks = [
				{ 
					value: 'init', 
					title: 'Early Site Initialization',
					badge: 'init',
					desc: 'Runs early on all frontend & backend requests. Recommended for custom post types and global hooks.'
				},
				{ 
					value: 'admin_init', 
					title: 'Admin Dashboard Init',
					badge: 'admin_init',
					desc: 'Runs only when loading backend admin pages. Best for dashboard customizations or admin menus.'
				},
				{ 
					value: 'wp_loaded', 
					title: 'WordPress Fully Loaded',
					badge: 'wp_loaded',
					desc: 'Executes after WordPress is completely loaded and environment setup is finished.'
				}
			];
		} else {
			hooks = [
				{ 
					value: 'template_redirect', 
					title: 'Default Page Load',
					badge: 'template_redirect',
					desc: 'Fires before template rendering. Best for redirects, headers, or general logic targeting this page.'
				},
				{ 
					value: 'init', 
					title: 'Early Request Load',
					badge: 'init',
					desc: 'Fires early on page requests, before template redirects are parsed.'
				},
				{ 
					value: 'admin_init', 
					title: 'Admin Request Hook',
					badge: 'admin_init',
					desc: 'Fires only if this page triggers a backend admin action (rarely used for frontend pages).'
				},
				{ 
					value: 'wp_enqueue_scripts', 
					title: 'Enqueue Assets (Styles/Scripts)',
					badge: 'wp_enqueue_scripts',
					desc: 'Fires when styles and scripts are loaded. Perfect for loading stylesheets or scripts.'
				},
				{ 
					value: 'wp_head', 
					title: 'HTML Head Section',
					badge: 'wp_head',
					desc: 'Fires in the HTML <head> section. Ideal for metadata, pixels, or custom tags.'
				},
				{ 
					value: 'wp_footer', 
					title: 'HTML Footer Section',
					badge: 'wp_footer',
					desc: 'Fires near the closing </body> tag. Recommended for analytics scripts or deferred JS.'
				}
			];
		}
		
		// Find active hook details
		var activeHook = hooks.find(function(h) { return h.value === localPhpHook; }) || hooks[0];
		
		// Set hidden input value
		$hiddenInput.val(activeHook.value);
		
		// Update Trigger Button
		$('#ccr-php-hook-trigger .ccr-selected-value-title').text(activeHook.title);
		$('#ccr-php-hook-trigger .ccr-selected-value-badge').text(activeHook.badge);
		
		// Render list options
		$.each(hooks, function(index, hook) {
			var isSelected = (hook.value === activeHook.value) ? 'selected' : '';
			var $option = $('<div class="ccr-custom-select-option ' + isSelected + '" data-value="' + hook.value + '">' +
				'<div class="ccr-option-header">' +
					'<span class="ccr-option-title">' + hook.title + '</span>' +
					'<span class="ccr-option-badge">' + hook.badge + '</span>' +
				'</div>' +
				'<div class="ccr-option-description">' + hook.desc + '</div>' +
			'</div>');
			
			$optionsContainer.append($option);
		});
		
		updateHookDescription(activeHook.value);
	}

	/**
	 * Update the PHP hook description text.
	 */
	function updateHookDescription(hook) {
		var desc = '';
		switch (hook) {
			case 'init':
				desc = 'Executes early plugin and request initialization.';
				break;
			case 'admin_init':
				desc = 'Executes during WordPress backend initialization.';
				break;
			case 'wp_loaded':
				desc = 'Executes once WordPress is fully set up.';
				break;
			case 'template_redirect':
				desc = 'Executes before templates load (recommended for redirects/headers).';
				break;
			case 'wp_enqueue_scripts':
				desc = 'Executes when styles and scripts are loaded.';
				break;
			case 'wp_head':
				desc = 'Executes in the HTML head (useful for inline styles/scripts/meta).';
				break;
			case 'wp_footer':
				desc = 'Executes in the HTML footer (useful for analytics/tracking).';
				break;
		}
		$('.ccr-hook-description').text(desc);
	}

	// Change listener for the hidden input (to keep local cache in sync)
	$(document).on('change', '#ccr-php-hook', function() {
		localPhpHook = $(this).val();
		updateHookDescription(localPhpHook);
	});

	// Toggle custom dropdown
	$(document).on('click', '#ccr-php-hook-trigger', function(e) {
		e.stopPropagation();
		$('.ccr-custom-select-wrapper').toggleClass('open');
	});

	// Close dropdown when clicking outside
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.ccr-custom-select-wrapper').length) {
			$('.ccr-custom-select-wrapper').removeClass('open');
		}
	});

	// Click option handler
	$(document).on('click', '.ccr-custom-select-option', function() {
		var $option = $(this);
		var val = $option.data('value');
		var title = $option.find('.ccr-option-title').text();
		var badge = $option.find('.ccr-option-badge').text();

		// Update Selection Visuals
		$('.ccr-custom-select-option').removeClass('selected');
		$option.addClass('selected');

		// Close menu
		$('.ccr-custom-select-wrapper').removeClass('open');

		// Set hidden input value and trigger change event
		var $hiddenInput = $('#ccr-php-hook');
		if ($hiddenInput.val() !== val) {
			$hiddenInput.val(val).trigger('change');
		}

		// Update Trigger Button Text
		$('#ccr-php-hook-trigger .ccr-selected-value-title').text(title);
		$('#ccr-php-hook-trigger .ccr-selected-value-badge').text(badge);
	});

	// Change listener for load context radio buttons
	$('input[name="ccr-context"]').on('change', function() {
		var newContext = $(this).val();
		if (newContext === activeContext) {
			return;
		}
		
		// Cache current editor value into the old active context
		var cm = getCodeMirror();
		var currentVal = cm ? cm.getValue() : $('#ccr-custom-editor').val();
		setCachedValue(activeTab, activeContext, currentVal);
		
		// Update active context
		activeContext = newContext;
		
		// Load newly selected context value
		var newVal = getCachedValue(activeTab, activeContext);
		if (cm) {
			cm.setValue(newVal);
			cm.clearHistory();
			setTimeout(function() {
				cm.refresh();
				cm.focus();
			}, 50);
		} else {
			$('#ccr-custom-editor').val(newVal);
		}
	});

	/**
	 * Toggle the editor tab visibility states.
	 */
	function handleTabUiStates() {
		var cm = getCodeMirror();
		var $footer = $('.ccr-editor-footer');
		
		$codeEditorContainer.show();
		$footer.show();
		$contextContainer.css('display', 'flex');
		$saveBtn.prop('disabled', false);
		if (cm) {
			cm.setOption('readOnly', false);
		}

		// Update checked radio button based on activeContext
		$('input[name="ccr-context"][value="' + activeContext + '"]').prop('checked', true);
	}



	/**
	 * Switch Editor Tab
	 */
	$('.ccr-tab-btn').on('click', function() {
		var $btn = $(this);
		var targetTab = $btn.data('tab');

		if (targetTab === activeTab) {
			return;
		}

		// Save current editor content to local cache
		var cm = getCodeMirror();
		var currentVal = cm ? cm.getValue() : $('#ccr-custom-editor').val();
		setCachedValue(activeTab, activeContext, currentVal);

		// Update active state in UI
		$('.ccr-tab-btn').removeClass('active');
		$btn.addClass('active');
		activeTab = targetTab;

		// Sync URL state parameters
		updateUrlParams(activePageId, activeTab);

		// Handle layout, visibility, and readonly states
		handleTabUiStates();

		// Switch mode and load content
		var cm = getCodeMirror();
		if (cm) {
			var mode = activeTab === 'css' ? 'text/css' : 'application/javascript';
			cm.setOption('mode', mode);
			var content = getCachedValue(activeTab, activeContext);
			cm.setValue(content);
			
			// Clear undo history specifically for this view swap so undo doesn't restore old mode data
			cm.clearHistory();
			
			setTimeout(function() {
				cm.refresh();
				cm.focus();
			}, 50);
		} else {
			var content = getCachedValue(activeTab, activeContext);
			$('#ccr-custom-editor').val(content);
		}
	});

	/**
	 * Click handler for page items in sidebar.
	 */
	$(document).on('click', '.ccr-page-item', function() {
		if (isSaving) {
			return; // Prevent switching pages while saving.
		}

		var $item = $(this);
		var pageId = $item.data('page-id');
		var pageTitle = $item.find('.ccr-page-title').text();

		// Highlight selected item.
		$('.ccr-page-item').removeClass('active');
		$item.addClass('active');

		activePageId = pageId;

		// Set active tab based on startTab or preserve current activeTab
		if (startTab) {
			activeTab = startTab;
			startTab = null; // Clear startup param once consumed
		}

		// Sync URL state parameters
		updateUrlParams(activePageId, activeTab);

		$('.ccr-tab-btn').removeClass('active');
		$('.ccr-tab-btn[data-tab="' + activeTab + '"]').addClass('active');



		// Show loading state in editor area.
		$formWrapper.hide();
		$emptyState.find('h3').text(ccrData.strings.loading);
		$emptyState.find('p').text('');
		$emptyState.find('.dashicons').attr('class', 'dashicons dashicons-update ccr-btn-spinner').css('font-size', '48px');
		$emptyState.show();

		// Fetch page data via AJAX.
		var getUrl = ccrData.isSafeMode ? ccrData.ajaxUrl + (ccrData.ajaxUrl.indexOf('?') !== -1 ? '&' : '?') + 'ccr_safe_mode=1' : ccrData.ajaxUrl;
		$.ajax({
			url: getUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'ccr_get_page_code',
				page_id: pageId,
				nonce: ccrData.nonce,
				ccr_safe_mode: ccrData.isSafeMode ? '1' : '0'
			},
			success: function(response) {
				// Revert empty state content/classes.
				$emptyState.find('.dashicons').attr('class', 'dashicons dashicons-layout').css('font-size', '');
				$emptyState.find('h3').text(ccrData.strings.selectPage);

				if (response.success) {
					// Cache the loaded code values
					localCssFrontend = response.data.css_frontend || '';
					localCssAdmin    = response.data.css_admin || '';
					localCssBoth     = response.data.css_both || '';
					localJsFrontend  = response.data.js_frontend || '';
					localJsAdmin     = response.data.js_admin || '';
					localJsBoth      = response.data.js_both || '';

					// Update form details.
					$pageTitle.text(pageTitle);
					if (pageId === 'global') {
						$viewPageLink.hide();
					} else {
						$viewPageLink.show().attr('href', response.data.view_url);
					}
					$codeToggle.prop('checked', response.data.is_enabled);

					// Reveal editor.
					$emptyState.hide();
					$formWrapper.show();

					// Handle layout, visibility, and readonly states
					handleTabUiStates();

					// Load active tab content into CodeMirror.
					var cm = getCodeMirror();
					if (cm) {
						var mode = activeTab === 'css' ? 'text/css' : 'application/javascript';
						cm.setOption('mode', mode);
						var content = getCachedValue(activeTab, activeContext);
						cm.setValue(content);
						
						// Clear history so undo doesn't go to previous page content.
						cm.clearHistory();
						
						// Delay refresh slightly to ensure correct editor sizing calculations.
						setTimeout(function() {
							cm.refresh();
							cm.focus();
						}, 50);
					} else {
						// Fallback if CodeMirror fails to load.
						var content = getCachedValue(activeTab, activeContext);
						$('#ccr-custom-editor').val(content);
					}
				} else {
					showToast(response.data.message || ccrData.strings.loadError, 'error');
				}
			},
			error: function() {
				// Revert empty state.
				$emptyState.find('.dashicons').attr('class', 'dashicons dashicons-layout').css('font-size', '');
				$emptyState.find('h3').text(ccrData.strings.selectPage);
				showToast(ccrData.strings.loadError, 'error');
			}
		});
	});

	/**
	 * Save Code action.
	 */
	$saveBtn.on('click', function(e) {
		e.preventDefault();
		if (!activePageId || isSaving) {
			return;
		}

		// Sync current CodeMirror text to cache variables
		var cm = getCodeMirror();
		var currentVal = cm ? cm.getValue() : $('#ccr-custom-editor').val();
		setCachedValue(activeTab, activeContext, currentVal);

		isSaving = true;
		$saveBtn.prop('disabled', true);
		$btnSpinner.show();

		var isEnabled = $codeToggle.is(':checked');

		// Wrapper save function
		var executeSave = function() {
			var saveUrl = ccrData.isSafeMode ? ccrData.ajaxUrl + (ccrData.ajaxUrl.indexOf('?') !== -1 ? '&' : '?') + 'ccr_safe_mode=1' : ccrData.ajaxUrl;
			$.ajax({
				url: saveUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'ccr_save_page_code',
					page_id: activePageId,
					css_frontend: localCssFrontend,
					css_admin: localCssAdmin,
					css_both: localCssBoth,
					js_frontend: localJsFrontend,
					js_admin: localJsAdmin,
					js_both: localJsBoth,
					is_enabled: isEnabled,

					nonce: ccrData.nonce,
					ccr_safe_mode: ccrData.isSafeMode ? '1' : '0'
				},
				success: function(response) {
					isSaving = false;
					$saveBtn.prop('disabled', false);
					$btnSpinner.hide();

					if (response.success) {
						showToast(ccrData.strings.saveSuccess, 'success');
						
						// Find the active sidebar list item and update its status styles.
						var $activeItem = $('.ccr-page-item.active');
						$activeItem.removeClass('code-active code-paused');
						if (response.data.status_class) {
							$activeItem.addClass(response.data.status_class);
						}

						// Update status dot tooltip.
						var tooltip = '';
						if (response.data.status_class === 'code-active') {
							tooltip = 'Custom CSS/JS is active on this page';
						} else if (response.data.status_class === 'code-paused') {
							tooltip = 'Custom CSS/JS is paused/disabled for this page';
						} else {
							tooltip = 'No custom CSS/JS configured';
						}
						$activeItem.find('.ccr-status-dot').attr('title', tooltip);
					} else {
						showToast(response.data.message || ccrData.strings.saveError, 'error');
					}
					
					// Recheck UI states in case permissions dynamically changed
					handleTabUiStates();
				},
				error: function() {
					isSaving = false;
					$saveBtn.prop('disabled', false);
					$btnSpinner.hide();
					showToast(ccrData.strings.saveError, 'error');
				}
			});
		};

		executeSave();
	});

	/**
	 * Client-side search/filtering of pages in sidebar.
	 */
	$('#ccr-page-search').on('keyup input', function() {
		var query = $(this).val().toLowerCase().trim();

		$('#ccr-page-list .ccr-page-item').each(function() {
			var $item = $(this);
			var title = $item.find('.ccr-page-title').text().toLowerCase();
			var slug = $item.find('.ccr-page-slug').text().toLowerCase();

			if (title.indexOf(query) !== -1 || slug.indexOf(query) !== -1) {
				$item.show();
			} else {
				$item.hide();
			}
		});
	});

	// Parse startup parameters for state restoration
	var startPage = getUrlParameter('ccr_page');
	var startTab = getUrlParameter('ccr_tab');

	if (startPage) {
		var $targetItem = $('.ccr-page-item[data-page-id="' + startPage + '"]');
		if ($targetItem.length > 0) {
			$targetItem.trigger('click');
		}
	}
});
