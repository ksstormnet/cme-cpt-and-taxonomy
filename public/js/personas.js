/**
 * Personas Frontend Scripts
 *
 * @param {Function} $ - jQuery function
 * @version 1.1.0
 */

/* global cmePersonas */

(function ($) {
	'use strict';

	/**
	 * Persona Handler
	 */
	const PersonaHandler = {
		/**
		 * Initialize the persona handler.
		 */
		init() {
			// Initialize the persona switcher.
			this.initPersonaSwitcher();

			// Initialize persona-specific content.
			this.initPersonaContent();

			// Handle any URL parameters on page load.
			this.handleUrlParams();
		},

		/**
		 * Initialize the persona switcher.
		 */
		initPersonaSwitcher() {
			// Set the current persona in the dropdown.
			const currentPersona = cmePersonas.currentPersona || 'default';
			$('.persona-switcher select').val(currentPersona);

			// Handle persona switching.
			$(document).on(
				'change',
				'.persona-switcher select, [data-persona-switch]',
				function () {
					const persona = $(this).val();

					// Redirect with the persona parameter.
					const url = new URL(window.location.href);
					url.searchParams.set('persona', persona);
					window.location.href = url.toString();
				}
			);
		},

		/**
		 * Initialize persona-specific content.
		 */
		initPersonaContent() {
			const currentPersona = cmePersonas.currentPersona || 'default';

			// Hide any persona-specific content that doesn't match the current persona.
			$('.persona-content').each(function () {
				const persona = $(this).data('persona');

				if (
					persona &&
					persona !== currentPersona &&
					persona !== 'default'
				) {
					$(this).hide();
				}
			});
		},

		/**
		 * Handle URL parameters.
		 */
		handleUrlParams() {
			const urlParams = new URLSearchParams(window.location.search);
			const persona = urlParams.get('persona');

			// If the persona parameter exists and it's different from the current persona,
			// we need to refresh the content.
			if (persona && persona !== cmePersonas.currentPersona) {
				// We'll use AJAX to refresh the content without reloading the page.
				$.ajax({
					url: cmePersonas.ajaxUrl,
					type: 'POST',
					data: {
						action: 'cme_set_persona',
						persona,
						nonce: cmePersonas.nonce,
					},
					success(response) {
						if (response.success) {
							// Reload the page without the persona parameter.
							const url = new URL(window.location.href);
							url.searchParams.delete('persona');
							window.location.href = url.toString();
						}
					},
				});
			}
		},
	};

	// Initialize when the document is ready.
	$(document).ready(function () {
		PersonaHandler.init();
	});
})(jQuery);
