/**
 * Personas Admin Scripts
 *
 * @package    CME_Personas
 * @version    1.1.0
 */

(function($) {
    'use strict';

    /**
     * Persona Admin Handler
     */
    const PersonaAdmin = {
        /**
         * Initialize the persona admin functionality.
         */
        init: function() {
            // Initialize the persona content editor tabs.
            this.initPersonaContentTabs();

            // Add AJAX handlers for content operations.
            this.setupAjaxHandlers();
        },

        /**
         * Initialize the persona content editor tabs.
         */
        initPersonaContentTabs: function() {
            // Handle tab switching.
            $(document).on('click', '.cme-persona-tab', function() {
                const persona = $(this).data('persona');

                // Hide all panels and deactivate all tabs.
                $('.cme-persona-tab-panel').hide();
                $('.cme-persona-tab').removeClass('active');

                // Show the selected panel and activate the tab.
                $('.cme-persona-tab-panel[data-persona="' + persona + '"]').show();
                $(this).addClass('active');
            });

            // Show the first tab by default.
            $('.cme-persona-tab:first').click();

            // Handle delete content button.
            $(document).on('click', '.cme-persona-delete', function() {
                if (confirm(cmePersonasAdmin.i18n.confirmDelete)) {
                    const persona = $(this).data('persona');

                    // Clear the form fields.
                    $('#cme_persona_' + persona + '_title').val('');

                    // For the content editor, we need to use the tinymce API.
                    if (typeof tinymce !== 'undefined' && tinymce.get('cme_persona_' + persona + '_content')) {
                        tinymce.get('cme_persona_' + persona + '_content').setContent('');
                    } else {
                        $('#cme_persona_' + persona + '_content').val('');
                    }

                    $('#cme_persona_' + persona + '_excerpt').val('');

                    // Remove the has-content class from the tab.
                    $('.cme-persona-tab[data-persona="' + persona + '"]').removeClass('has-content');
                }
            });
        },

        /**
         * Set up AJAX handlers for content operations.
         */
        setupAjaxHandlers: function() {
            // Initialize content preview.
            $(document).on('click', '.preview-persona-content', function(e) {
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
         * @param {number} postId   The post ID.
         * @param {string} persona  The persona ID.
         */
        showContentPreview: function(postId, persona) {
            $.ajax({
                url: cmePersonasAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cme_preview_persona_content',
                    post_id: postId,
                    persona: persona,
                    nonce: cmePersonasAdmin.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Create a dialog to show the preview.
                        const $dialog = $('<div class="cme-persona-preview-dialog"></div>');
                        $dialog.html(response.data);

                        // Append the dialog to the body.
                        $('body').append($dialog);

                        // Initialize the dialog.
                        $dialog.dialog({
                            title: cmePersonasAdmin.i18n.previewTitle.replace('%s', persona),
                            width: 800,
                            height: 600,
                            modal: true,
                            buttons: {
                                [cmePersonasAdmin.i18n.closeButton]: function() {
                                    $(this).dialog('close');
                                }
                            },
                            close: function() {
                                $(this).dialog('destroy').remove();
                            }
                        });
                    } else {
                        alert(cmePersonasAdmin.i18n.previewError);
                    }
                },
                error: function() {
                    alert(cmePersonasAdmin.i18n.previewError);
                }
            });
        }
    };

    // Initialize when the document is ready.
    $(document).ready(function() {
        PersonaAdmin.init();
    });

})(jQuery);
