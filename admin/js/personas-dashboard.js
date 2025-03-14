/**
 * Personas Dashboard JavaScript
 *
 * @version 1.5.2
 * @param {Object} $ jQuery object
 */

( function( $ ) {
	'use strict';

	/**
	 * Initialize the image rotators for persona cards
	 */
	function initPersonaRotators() {
		$( '.cme-persona-image-rotator' ).each( function() {
			const rotator = $( this );
			const container = rotator.find( '.cme-persona-image-container' );
			const slides = container.find( '.cme-persona-slide' );

			// Bail early if no slides to rotate
			if ( slides.length <= 0 ) {
				return;
			}

			const dotsContainer = rotator.find( '.cme-persona-rotator-dots' );
			const prevBtn = rotator.find( '.cme-persona-rotator-prev' );
			const nextBtn = rotator.find( '.cme-persona-rotator-next' );
			let currentIndex = 0;
			let autoRotateInterval;

			// Create dots based on number of slides
			slides.each( function( index ) {
				const dot = $( '<div class="cme-persona-rotator-dot"></div>' );
				if ( index === 0 ) {
					dot.addClass( 'active' );
				}
				dotsContainer.append( dot );

				// Add click handler to each dot
				dot.on( 'click', function() {
					goToSlide( index );
				} );
			} );

			const dots = dotsContainer.find( '.cme-persona-rotator-dot' );

			// Make first slide active initially
			slides.eq( 0 ).addClass( 'active' );

			// Set up click handlers for navigation buttons
			prevBtn.on( 'click', function() {
				goToSlide( currentIndex - 1 );
			} );

			nextBtn.on( 'click', function() {
				goToSlide( currentIndex + 1 );
			} );

			// Add keyboard navigation
			rotator.attr( 'tabindex', '0' ).on( 'keydown', function( e ) {
				if ( e.key === 'ArrowLeft' ) {
					goToSlide( currentIndex - 1 );
				} else if ( e.key === 'ArrowRight' ) {
					goToSlide( currentIndex + 1 );
				}
			} );

			/**
			 * Navigate to a specific slide
			 *
			 * @param {number} index The slide index to show
			 */
			function goToSlide( index ) {
				// Reset auto-rotation timer
				clearInterval( autoRotateInterval );

				// Handle looping
				if ( index < 0 ) {
					index = slides.length - 1;
				} else if ( index >= slides.length ) {
					index = 0;
				}

				// Remove active class from current slide and dot
				slides.eq( currentIndex ).removeClass( 'active' );
				dots.eq( currentIndex ).removeClass( 'active' );

				// Update current index
				currentIndex = index;

				// Add active class to new slide and dot
				slides.eq( currentIndex ).addClass( 'active' );
				dots.eq( currentIndex ).addClass( 'active' );

				// Restart auto-rotation
				startAutoRotate();
			}

			/**
			 * Start auto-rotation of slides
			 */
			function startAutoRotate() {
				// Only setup auto-rotation if we have more than one slide
				if ( slides.length > 1 ) {
					autoRotateInterval = setInterval( function() {
						goToSlide( currentIndex + 1 );
					}, 5000 );
				}
			}

			// Start auto-rotation
			startAutoRotate();

			// Pause auto-rotation on hover
			rotator.on( 'mouseenter focus', function() {
				clearInterval( autoRotateInterval );
			} );

			// Resume auto-rotation when mouse leaves or blur
			rotator.on( 'mouseleave blur', function() {
				startAutoRotate();
			} );
		} );
	}

	// Initialize components when the DOM is ready
	$( document ).ready( function() {
		initPersonaRotators();
	} );
} )( jQuery );
