/**
 * Frontend JavaScript for Persona Content
 *
 * Handles basic persona functionality.
 *
 * @package
 * @version    1.6.0
 */

/* global cmePersonas */
(function ($) {
	'use strict';

	/**
	 * Persona Frontend Handler
	 */
	const PersonaFrontend = {
		/**
		 * Current persona
		 */
		currentPersona: '',

		/**
		 * Initialize the frontend functionality
		 */
		init() {
			// Store the current persona
			this.currentPersona = cmePersonas.currentPersona || 'default';
		},
	};

	// Initialize when the document is ready
	$(document).ready(function () {
		PersonaFrontend.init();
	});
})(jQuery);
