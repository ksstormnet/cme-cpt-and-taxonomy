/**
 * Personas Admin Scripts
 *
 * @param {Function} $ - jQuery function
 * @version 1.2.0
 */

/* global cmePersonasAdmin */

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
			// Initialize Gutenberg integration if the block editor is present
			this.initBlockEditorIntegration();
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
						// Clear any cached content
						if (window.localStorage) {
							const keys = Object.keys(window.localStorage);
							const cmeKeysPattern = /^cme_/;

							keys.forEach((key) => {
								if (cmeKeysPattern.test(key)) {
									window.localStorage.removeItem(key);
								}
							});
						}
					}
				});
			}
		},
	};

	// Initialize when the document is ready.
	$(document).ready(function () {
		PersonaAdmin.init();
	});
})(jQuery);
