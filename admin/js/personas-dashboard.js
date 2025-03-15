/**
 * Personas Dashboard JavaScript
 *
 * @version 1.5.3
 * @param {Object} $ jQuery object
 */

(function ($) {
	'use strict';

	/**
	 * Initialize the image rotators for persona cards
	 */
	function initPersonaRotators() {
		$('.cme-persona-image-rotator').each(function () {
			const rotator = $(this);
			const container = rotator.find('.cme-persona-image-container');
			const slides = container.find('.cme-persona-slide');

			// Bail early if no slides to rotate
			if (slides.length <= 0) {
				return;
			}

			const dotsContainer = rotator.find('.cme-persona-rotator-dots');
			const prevBtn = rotator.find('.cme-persona-rotator-prev');
			const nextBtn = rotator.find('.cme-persona-rotator-next');
			let currentIndex = 0;
			let autoRotateInterval;

			// Create dots based on number of slides
			slides.each(function (index) {
				// Create dot with role="tab" for accessibility
				const dot = $(
					'<div class="cme-persona-rotator-dot" role="tab" tabindex="0"></div>'
				);
				if (index === 0) {
					dot.addClass('active');
					dot.attr('aria-current', 'true');
					dot.attr('aria-selected', 'true');
				} else {
					dot.attr('aria-current', 'false');
					dot.attr('aria-selected', 'false');
				}

				// Add unique ID and aria-controls to connect dots with slides
				const slideId =
					'persona-slide-' + rotator.attr('id') + '-' + index;
				slides.eq(index).attr('id', slideId);
				dot.attr('aria-controls', slideId);

				dotsContainer.append(dot);

				// Add click handler to each dot
				dot.on('click', function () {
					goToSlide(index);
				});

				// Add keyboard handler to each dot
				dot.on('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						goToSlide(index);
					}
				});
			});

			const dots = dotsContainer.find('.cme-persona-rotator-dot');

			// Make first slide active initially
			slides.eq(0).addClass('active');
			slides.attr('aria-hidden', 'true');
			slides.eq(0).attr('aria-hidden', 'false');

			// Set up click handlers for navigation buttons
			prevBtn.on('click', function () {
				goToSlide(currentIndex - 1);
			});

			nextBtn.on('click', function () {
				goToSlide(currentIndex + 1);
			});

			// Add keyboard navigation
			rotator.attr('tabindex', '0').on('keydown', function (e) {
				if (e.key === 'ArrowLeft') {
					goToSlide(currentIndex - 1);
				} else if (e.key === 'ArrowRight') {
					goToSlide(currentIndex + 1);
				}
			});

			// Set up touch support
			setupTouchSupport(
				rotator,
				container,
				function () {
					goToSlide(currentIndex - 1);
				},
				function () {
					goToSlide(currentIndex + 1);
				}
			);

			/**
			 * Navigate to a specific slide
			 *
			 * @param {number} index The slide index to show
			 */
			function goToSlide(index) {
				// Reset auto-rotation timer
				clearInterval(autoRotateInterval);

				// Handle looping
				if (index < 0) {
					index = slides.length - 1;
				} else if (index >= slides.length) {
					index = 0;
				}

				// Remove active class from current slide and dot
				slides.eq(currentIndex).removeClass('active');
				dots.eq(currentIndex).removeClass('active');

				// Remove ARIA attributes from current slide and dot
				slides.eq(currentIndex).attr('aria-hidden', 'true');
				dots.eq(currentIndex).attr('aria-current', 'false');
				dots.eq(currentIndex).attr('aria-selected', 'false');

				// Update current index
				currentIndex = index;

				// Add active class to new slide and dot
				slides.eq(currentIndex).addClass('active');
				dots.eq(currentIndex).addClass('active');

				// Add ARIA attributes to new slide and dot
				slides.eq(currentIndex).attr('aria-hidden', 'false');
				dots.eq(currentIndex).attr('aria-current', 'true');
				dots.eq(currentIndex).attr('aria-selected', 'true');

				// Focus the active dot if this was triggered by keyboard navigation
				if (rotator[0].ownerDocument.activeElement === rotator[0]) {
					dots.eq(currentIndex).focus();
				}

				// Restart auto-rotation
				startAutoRotate();

				// Announce slide change to screen readers
				rotator.find('.cme-persona-rotator-announcement').remove();
				const slideCaption = slides
					.eq(currentIndex)
					.find('.cme-persona-slide-caption')
					.text();
				$(
					'<div class="cme-persona-rotator-announcement sr-only" aria-live="polite"></div>'
				)
					.text('Showing image: ' + slideCaption)
					.appendTo(rotator);
			}

			/**
			 * Start auto-rotation of slides
			 */
			function startAutoRotate() {
				// Only setup auto-rotation if we have more than one slide
				if (slides.length > 1) {
					autoRotateInterval = setInterval(function () {
						goToSlide(currentIndex + 1);
					}, 5000);
				}
			}

			// Start auto-rotation
			startAutoRotate();

			// Pause auto-rotation on hover
			rotator.on('mouseenter focus', function () {
				clearInterval(autoRotateInterval);
			});

			// Resume auto-rotation when mouse leaves or blur
			rotator.on('mouseleave blur', function () {
				startAutoRotate();
			});

			// No need to call setupTouchSupport here as it's already been setup above
		});
	}

	/**
	 * Set up touch swipe support for rotators
	 *
	 * @param {jQuery}   rotator      The rotator element
	 * @param {jQuery}   container    The slide container element
	 * @param {Function} prevFunction Function to go to previous slide
	 * @param {Function} nextFunction Function to go to next slide
	 */
	function setupTouchSupport(rotator, container, prevFunction, nextFunction) {
		let touchStartX = 0;
		let touchEndX = 0;
		let touchStartY = 0;
		let touchEndY = 0;
		let isSwiping = false;

		container.on('touchstart', function (e) {
			touchStartX = e.originalEvent.touches[0].clientX;
			touchStartY = e.originalEvent.touches[0].clientY;
			isSwiping = true;
		});

		container.on('touchmove', function (e) {
			if (!isSwiping) {
				return;
			}

			const touchX = e.originalEvent.touches[0].clientX;
			const touchY = e.originalEvent.touches[0].clientY;

			// Determine if scrolling vertically or swiping horizontally
			const isScrollingVertical =
				Math.abs(touchY - touchStartY) > Math.abs(touchX - touchStartX);

			// If scrolling vertically, don't interfere with page scrolling
			if (isScrollingVertical) {
				isSwiping = false;
				return;
			}

			// Prevent page scrolling when swiping horizontally
			e.preventDefault();
		});

		container.on('touchend', function (e) {
			if (!isSwiping) {
				return;
			}

			touchEndX = e.originalEvent.changedTouches[0].clientX;
			touchEndY = e.originalEvent.changedTouches[0].clientY;

			handleSwipe();
			isSwiping = false;
		});

		function handleSwipe() {
			const SWIPE_THRESHOLD = 50; // Minimum swipe distance in pixels
			const horizontalSwipeDistance = touchStartX - touchEndX;
			const verticalSwipeDistance = touchStartY - touchEndY;

			// Ensure the swipe is more horizontal than vertical
			if (
				Math.abs(horizontalSwipeDistance) >
				Math.abs(verticalSwipeDistance)
			) {
				if (horizontalSwipeDistance > SWIPE_THRESHOLD) {
					// Swipe left, go next
					nextFunction();
				} else if (horizontalSwipeDistance < -SWIPE_THRESHOLD) {
					// Swipe right, go previous
					prevFunction();
				}
			}
		}
	}

	// Add CSS for screen reader only content
	$(
		'<style>.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }</style>'
	).appendTo('head');

	// Initialize components when the DOM is ready
	$(document).ready(function () {
		// Generate unique IDs for each rotator
		$('.cme-persona-image-rotator').each(function (index) {
			$(this).attr('id', 'persona-rotator-' + index);
		});

		// Initialize rotators
		initPersonaRotators();
	});

	// Handle image loading optimization
	$(window).on('load', function () {
		// Preload next image after initial load is complete
		$('.cme-persona-slide-image').each(function () {
			const img = new Image();
			img.src = $(this).attr('src');
		});
	});
})(jQuery);
