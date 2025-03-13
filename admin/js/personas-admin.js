/**
 * Personas Admin Scripts
 *
 * @param {Function} $ - jQuery function
 * @version 1.2.0
 */

/* global cmePersonasAdmin, tinymce */

(function ($) {
	'use strict';

	/**
	 * Persona Admin Handler
	 */
	const PersonaAdmin = {
		/**
		 * Initialize the persona admin functionality.
		 */
		init() {
			// Initialize the persona content editor tabs.
			this.initPersonaContentTabs();

			// Initialize Gutenberg integration if the block editor is present
			this.initBlockEditorIntegration();

			// Add AJAX handlers for content operations.
			this.setupAjaxHandlers();
		},

		/**
		 * Initialize integration with the Gutenberg block editor.
		 */
		initBlockEditorIntegration() {
			// Check if we're in the block editor
			if (
				typeof window.wp !== 'undefined' &&
				window.wp.data &&
				window.wp.data.select('core/editor')
			) {
				// Listen for document changes
				window.wp.data.subscribe(() => {
					const isSavingPost = window.wp.data
						.select('core/editor')
						.isSavingPost();
					const isAutosavingPost = window.wp.data
						.select('core/editor')
						.isAutosavingPost();

					// Only proceed if we're actually saving the post (not autosaving)
					if (isSavingPost && !isAutosavingPost) {
						// Clear any cached persona content
						if (window.localStorage) {
							const keys = Object.keys(window.localStorage);
							const personaKeysPattern = /^cme_persona_content_/;

							keys.forEach((key) => {
								if (personaKeysPattern.test(key)) {
									window.localStorage.removeItem(key);
								}
							});
						}
					}
				});
			}
		},

		/**
		 * Initialize the persona content editor tabs.
		 */
		initPersonaContentTabs() {
			// Handle tab switching.
			$(document).on('click', '.cme-persona-tab', function () {
				const persona = $(this).data('persona');

				// Hide all panels and deactivate all tabs.
				$('.cme-persona-tab-panel').hide();
				$('.cme-persona-tab').removeClass('active');

				// Show the selected panel and activate the tab.
				$(
					'.cme-persona-tab-panel[data-persona="' + persona + '"]'
				).show();
				$(this).addClass('active');
			});

			// Show the first tab by default.
			$('.cme-persona-tab:first').click();

			// Handle delete content button.
			$(document).on('click', '.cme-persona-delete', function () {
				// eslint-disable-next-line no-alert
				const confirmDelete = window.confirm(
					cmePersonasAdmin.i18n.confirmDelete
				);
				if (confirmDelete) {
					const persona = $(this).data('persona');

					// Clear the form fields.
					$('#cme_persona_' + persona + '_title').val('');

					// For the content editor, we need to use the tinymce API.
					if (
						typeof tinymce !== 'undefined' &&
						tinymce.get('cme_persona_' + persona + '_content')
					) {
						tinymce
							.get('cme_persona_' + persona + '_content')
							.setContent('');
					} else {
						$('#cme_persona_' + persona + '_content').val('');
					}

					$('#cme_persona_' + persona + '_excerpt').val('');

					// Remove the has-content class from the tab.
					$(
						'.cme-persona-tab[data-persona="' + persona + '"]'
					).removeClass('has-content');
				}
			});
		},

		/**
		 * Set up AJAX handlers for content operations.
		 */
		setupAjaxHandlers() {
			// Initialize content preview.
			$(document).on('click', '.preview-persona-content', function (e) {
				e.preventDefault();

				const postId = $(this).data('post-id');
				const persona = $(this).data('persona');

				// Show the preview dialog.
				PersonaAdmin.showContentPreview(postId, persona);
			});
		},

		/**
		 * Show a preview of persona-specific content.
		 *
		 * @param {number} postId  The post ID.
		 * @param {string} persona The persona ID.
		 */
		showContentPreview(postId, persona) {
			$.ajax({
				url: cmePersonasAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'cme_preview_persona_content',
					post_id: postId,
					persona,
					nonce: cmePersonasAdmin.nonce,
				},
				success(response) {
					if (response.success && response.data) {
						// Create a styled dialog to show the preview.
						const $dialog = $(
							'<div class="cme-persona-preview-dialog"></div>'
						);
						$dialog.html(response.data);

						// Append the dialog to the body.
						$('body').append($dialog);

						// Get the persona name for display
						const personaName =
							cmePersonasAdmin.personas[persona] || persona;

						// Initialize the dialog with enhanced styling.
						$dialog.dialog({
							title: cmePersonasAdmin.i18n.previewTitle.replace(
								'%s',
								personaName
							),
							width: 800,
							height: 600,
							modal: true,
							dialogClass: 'wp-dialog cme-preview-dialog',
							buttons: [
								{
									text: cmePersonasAdmin.i18n.closeButton,
									class: 'button button-primary',
									click() {
										$(this).dialog('close');
									},
								},
							],
							open() {
								// Add header to the preview
								const $header = $(
									'<div class="cme-preview-header"></div>'
								);

								$header.append(
									'<span class="cme-preview-badge">' +
										cmePersonasAdmin.i18n.previewBadge.replace(
											'%s',
											personaName
										) +
										'</span>'
								);
								$dialog.prepend($header);

								// Apply styling for WordPress admin UI
								$('.ui-dialog-titlebar').addClass(
									'wp-dialog-titlebar'
								);
								$('.ui-dialog-title').addClass(
									'wp-dialog-title'
								);
								$('.ui-dialog-buttonpane').addClass(
									'wp-dialog-buttonpane'
								);
							},
							close() {
								$(this).dialog('destroy').remove();
							},
						});
					} else {
						// eslint-disable-next-line no-alert
						window.alert(cmePersonasAdmin.i18n.previewError);
					}
				},
				error() {
					// eslint-disable-next-line no-alert
					window.alert(cmePersonasAdmin.i18n.previewError);
				},
			});
		},
	};

	// Initialize when the document is ready.
	$(document).ready(function () {
		PersonaAdmin.init();
	});
})(jQuery);
