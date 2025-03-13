/**
 * Frontend JavaScript for Persona functionality
 *
 * Handles persona switching and dynamic content updating.
 *
 * @package    CME_Personas
 * @version    1.4.0
 */

/* global cmePersonas */
(function($) {
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

      // Set up event handlers
      this.setupEventHandlers();

      // Initialize dynamic content sections
      this.initDynamicContent();
    },

    /**
     * Set up event handlers for persona switching
     */
    setupEventHandlers() {
      // Handle persona button clicks
      $(document).on('click', '.cme-persona-button', this.handlePersonaButtonClick.bind(this));

      // Handle persona dropdown changes
      $(document).on('change', '.cme-persona-select', this.handlePersonaDropdownChange.bind(this));
    },

    /**
     * Initialize dynamic content sections
     */
    initDynamicContent() {
      // Add active class to the current persona's button
      $(`.cme-persona-button[data-persona="${this.currentPersona}"]`).addClass('active');

      // Set the dropdown to the current persona
      $(`.cme-persona-select`).val(this.currentPersona);

      // Listen for custom event to refresh content after persona switch
      $(document).on('persona:switched', this.refreshContent.bind(this));
    },

    /**
     * Handle persona button click
     *
     * @param {Event} e The click event
     */
    handlePersonaButtonClick(e) {
      e.preventDefault();
      const $button = $(e.currentTarget);
      const persona = $button.data('persona');

      // Don't switch if it's already the current persona
      if (persona === this.currentPersona) {
        return;
      }

      // Switch persona via AJAX
      this.switchPersona(persona);
    },

    /**
     * Handle persona dropdown change
     *
     * @param {Event} e The change event
     */
    handlePersonaDropdownChange(e) {
      const persona = $(e.currentTarget).val();

      // Don't switch if it's already the current persona
      if (persona === this.currentPersona) {
        return;
      }

      // Switch persona via AJAX
      this.switchPersona(persona);
    },

    /**
     * Switch to a new persona
     *
     * @param {string} persona The persona ID to switch to
     */
    switchPersona(persona) {
      // Show loading state
      this.showLoading(true);

      // Make AJAX request to switch persona
      $.ajax({
        url: cmePersonas.ajaxUrl,
        type: 'POST',
        data: {
          action: 'cme_switch_persona',
          persona: persona,
          nonce: cmePersonas.nonce
        },
        success: (response) => {
          if (response.success) {
            // Update current persona
            this.currentPersona = persona;

            // Remove active class from all buttons
            $('.cme-persona-button').removeClass('active');

            // Add active class to the current persona's button
            $(`.cme-persona-button[data-persona="${persona}"]`).addClass('active');

            // Update the dropdown
            $(`.cme-persona-select`).val(persona);

            // Show success message if provided
            if (response.data && response.data.message) {
              this.showMessage(response.data.message, 'success');
            }

            // Trigger custom event for content refresh
            $(document).trigger('persona:switched', [persona]);

            // Optional: Reload the page for full content refresh
            if (window.cmePersonas && window.cmePersonas.reloadOnSwitch) {
              window.location.reload();
            }
          } else {
            // Show error message
            const errorMsg = response.data && response.data.message
              ? response.data.message
              : 'Failed to switch persona';
            this.showMessage(errorMsg, 'error');
          }

          // Hide loading state
          this.showLoading(false);
        },
        error: () => {
          // Show error message
          this.showMessage('An error occurred while switching personas', 'error');

          // Hide loading state
          this.showLoading(false);
        }
      });
    },

    /**
     * Refresh dynamic content after persona switch
     */
    refreshContent() {
      // Find all dynamic content sections that need refreshing
      $('.cme-persona-dynamic').each((index, element) => {
        const $element = $(element);
        const postId = $element.data('post-id');
        const field = $element.data('field') || 'content';

        // Only refresh if we have a post ID
        if (postId) {
          this.loadDynamicContent($element, postId, field);
        }
      });
    },

    /**
     * Load dynamic content for an element
     *
     * @param {jQuery} $element The element to update
     * @param {number} postId   The post ID
     * @param {string} field    The content field to load
     */
    loadDynamicContent($element, postId, field) {
      // Show loading indicator
      $element.addClass('loading');

      // Make AJAX request to get content
      $.ajax({
        url: cmePersonas.ajaxUrl,
        type: 'POST',
        data: {
          action: 'cme_get_persona_content',
          post_id: postId,
          field: field,
          persona: this.currentPersona,
          nonce: cmePersonas.nonce
        },
        success: (response) => {
          if (response.success && response.data) {
            // Update the element content
            $element.html(response.data.content);
          }

          // Remove loading indicator
          $element.removeClass('loading');
        },
        error: () => {
          // Remove loading indicator
          $element.removeClass('loading');
        }
      });
    },

    /**
     * Show a message to the user
     *
     * @param {string} message The message to show
     * @param {string} type    The message type (success, error)
     */
    showMessage(message, type) {
      // Create message element if it doesn't exist
      let $message = $('.cme-persona-message');
      if (!$message.length) {
        $message = $('<div class="cme-persona-message"></div>');
        $('body').append($message);
      }

      // Set message content and type
      $message.html(message).attr('class', `cme-persona-message ${type}`).fadeIn();

      // Hide after 3 seconds
      setTimeout(() => {
        $message.fadeOut();
      }, 3000);
    },

    /**
     * Show or hide loading state
     *
     * @param {boolean} show Whether to show or hide loading state
     */
    showLoading(show) {
      if (show) {
        // Add loading class to switchers
        $('.cme-persona-switcher').addClass('loading');
      } else {
        // Remove loading class from switchers
        $('.cme-persona-switcher').removeClass('loading');
      }
    }
  };

  // Initialize when the document is ready
  $(document).ready(function() {
    PersonaFrontend.init();
  });

})(jQuery);
