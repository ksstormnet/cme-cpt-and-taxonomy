/**
 * Persona Rotator Script for the Admin Dashboard
 *
 * Provides interactive image rotation for persona cards with keyboard,
 * mouse, and touch support with accessibility features.
 *
 * @since 1.5.3
 * @param {Object} $ - jQuery object
 */

(function ($) {
	'use strict';

	// Feature detection for reduced motion preference
	const prefersReducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;

	/**
	 * PersonaRotator class - Handles rotation of persona images
	 */
	class PersonaRotator {
		/**
		 * Constructor
		 *
		 * @param {HTMLElement} container - The rotator container element
		 */
		constructor(container) {
			// Elements
			this.container = $(container);
			this.slides = this.container.find('.cme-persona-slide');
			this.dotsContainer = this.container.find(
				'.cme-persona-rotator-dots'
			);
			this.prevButton = this.container.find('.cme-persona-rotator-prev');
			this.nextButton = this.container.find('.cme-persona-rotator-next');

			// State
			this.slideCount = this.slides.length;
			this.currentIndex = 0;
			this.isAnimating = false;
			this.touchStartX = 0;
			this.touchStartY = 0;
			this.touchEndX = 0;
			this.touchEndY = 0;

			// Preload images for smoother transitions
			this.preloadImages();

			// Initialize
			this.setupDots();
			this.bindEvents();
			this.updateAriaLive(
				'Persona rotator initialized with ' +
					this.slideCount +
					' images.'
			);
		}

		/**
		 * Preload all slide images
		 */
		preloadImages() {
			this.slides.each((index, slide) => {
				const img = $(slide).find('img');
				if (img.length) {
					const imgSrc = img.attr('src');
					if (imgSrc) {
						const preloadImg = new Image();
						preloadImg.src = imgSrc;
					}
				}
			});
		}

		/**
		 * Create dot indicators for each slide
		 */
		setupDots() {
			for (let i = 0; i < this.slideCount; i++) {
				const dot = $('<button>')
					.addClass('cme-persona-rotator-dot')
					.attr({
						type: 'button',
						'aria-label': `Show image ${i + 1} of ${this.slideCount}`,
						'aria-selected': i === 0 ? 'true' : 'false',
						role: 'tab',
						tabindex: i === 0 ? '0' : '-1',
					});

				this.dotsContainer.append(dot);
			}
			this.dots = this.dotsContainer.find('.cme-persona-rotator-dot');
		}

		/**
		 * Bind all event listeners
		 */
		bindEvents() {
			// Button navigation
			this.prevButton.on('click', this.prevSlide.bind(this));
			this.nextButton.on('click', this.nextSlide.bind(this));

			// Dot navigation
			this.dots.on('click', (e) => {
				const index = this.dots.index(e.currentTarget);
				this.goToSlide(index);
			});

			// Keyboard navigation for the whole rotator
			this.container.on('keydown', this.handleKeydown.bind(this));

			// Touch events
			this.container.on('touchstart', this.handleTouchStart.bind(this));
			this.container.on('touchmove', this.handleTouchMove.bind(this));
			this.container.on('touchend', this.handleTouchEnd.bind(this));

			// Focus management
			this.dots.on('keydown', this.handleDotKeydown.bind(this));
		}

		/**
		 * Handle keydown events for the container
		 *
		 * @param {Event} e - The keydown event
		 */
		handleKeydown(e) {
			switch (e.key) {
				case 'ArrowLeft':
					this.prevSlide();
					e.preventDefault();
					break;

				case 'ArrowRight':
					this.nextSlide();
					e.preventDefault();
					break;
			}
		}

		/**
		 * Handle keydown events for the dots
		 *
		 * @param {Event} e - The keydown event
		 */
		handleDotKeydown(e) {
			let newIndex = this.currentIndex;

			switch (e.key) {
				case 'ArrowLeft':
					newIndex = Math.max(0, this.currentIndex - 1);
					e.preventDefault();
					break;

				case 'ArrowRight':
					newIndex = Math.min(
						this.slideCount - 1,
						this.currentIndex + 1
					);
					e.preventDefault();
					break;

				case 'Home':
					newIndex = 0;
					e.preventDefault();
					break;

				case 'End':
					newIndex = this.slideCount - 1;
					e.preventDefault();
					break;

				default:
					return;
			}

			if (newIndex !== this.currentIndex) {
				this.goToSlide(newIndex);
				this.dots.eq(newIndex).focus();
			}
		}

		/**
		 * Handle touch start event
		 *
		 * @param {Event} e - The touchstart event
		 */
		handleTouchStart(e) {
			const touch = e.originalEvent.touches[0];
			this.touchStartX = touch.clientX;
			this.touchStartY = touch.clientY;
		}

		/**
		 * Handle touch move event
		 *
		 * @param {Event} e - The touchmove event
		 */
		handleTouchMove(e) {
			if (!this.touchStartX) {
				return;
			}

			const touch = e.originalEvent.touches[0];
			this.touchEndX = touch.clientX;
			this.touchEndY = touch.clientY;

			// Prevent vertical scrolling when swiping horizontally
			const diffX = Math.abs(this.touchEndX - this.touchStartX);
			const diffY = Math.abs(this.touchEndY - this.touchStartY);

			if (diffX > diffY && diffX > 30) {
				e.preventDefault();
			}
		}

		/**
		 * Handle touch end event
		 */
		handleTouchEnd() {
			if (!this.touchStartX || !this.touchEndX) {
				return;
			}

			const diffX = this.touchEndX - this.touchStartX;
			const diffY = this.touchEndY - this.touchStartY;

			// Only handle horizontal swipes, ignore vertical swipes
			if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
				if (diffX > 0) {
					this.prevSlide();
				} else {
					this.nextSlide();
				}
			}

			// Reset values
			this.touchStartX = 0;
			this.touchEndX = 0;
			this.touchStartY = 0;
			this.touchEndY = 0;
		}

		/**
		 * Navigate to the previous slide
		 */
		prevSlide() {
			const newIndex =
				(this.currentIndex - 1 + this.slideCount) % this.slideCount;
			this.goToSlide(newIndex);
		}

		/**
		 * Navigate to the next slide
		 */
		nextSlide() {
			const newIndex = (this.currentIndex + 1) % this.slideCount;
			this.goToSlide(newIndex);
		}

		/**
		 * Go to a specific slide
		 *
		 * @param {number} index - The slide index to navigate to
		 */
		goToSlide(index) {
			if (this.isAnimating || index === this.currentIndex) {
				return;
			}

			this.isAnimating = true;

			// Update slides
			this.slides.attr('aria-hidden', 'true');
			this.slides.eq(index).attr('aria-hidden', 'false');

			// Update dots
			this.dots.attr({
				'aria-selected': 'false',
				tabindex: '-1',
			});
			this.dots.eq(index).attr({
				'aria-selected': 'true',
				tabindex: '0',
			});

			// Get captions for screen reader announcement
			const caption =
				this.slides
					.eq(index)
					.find('.cme-persona-slide-caption')
					.text() || `Image ${index + 1}`;
			this.updateAriaLive(
				`Showing ${caption}, image ${index + 1} of ${this.slideCount}`
			);

			// Apply transition with or without animation based on reduced motion preference
			if (prefersReducedMotion) {
				this.applySlideChange(index);
			} else {
				this.slides.eq(this.currentIndex).fadeOut(200);
				this.slides.eq(index).fadeIn(200, () => {
					this.applySlideChange(index);
				});
			}
		}

		/**
		 * Apply slide change after transition
		 *
		 * @param {number} index - The new slide index
		 */
		applySlideChange(index) {
			this.currentIndex = index;
			this.isAnimating = false;
		}

		/**
		 * Update aria-live region for screen reader announcements
		 *
		 * @param {string} message - The message to announce
		 */
		updateAriaLive(message) {
			// If aria-live region doesn't exist, create it
			let liveRegion = this.container.find('.cme-sr-only');
			if (!liveRegion.length) {
				liveRegion = $('<div>', {
					class: 'cme-sr-only',
					'aria-live': 'polite',
					'aria-atomic': 'true',
				}).appendTo(this.container);
			}

			liveRegion.text(message);
		}
	}

	// Initialize all persona rotators when the document is ready
	$(document).ready(function () {
		$('.cme-persona-image-rotator').each(function () {
			new PersonaRotator(this);
		});
	});
})(jQuery);
