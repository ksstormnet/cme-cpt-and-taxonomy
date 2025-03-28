/**
 * Media Library enhancement for tag management.
 *
 * Adds media tag management directly to the Media Library interface.
 *
 * @param {Function} $ jQuery function.
 */

/* global cmeMediaTags */

(function ($) {
	'use strict';

	// Initialize once the DOM is ready
	$(document).ready(function () {
		// Initialize only on media library page
		if (window.location.href.indexOf('upload.php') === -1) {
			return;
		}

		// Add tags interface to media items
		enhanceMediaLibraryItems();

		// Re-initialize when switching between list and grid views
		$(document).on('click', '.view-switch a', function () {
			setTimeout(enhanceMediaLibraryItems, 500);
		});

		// Re-initialize when uploading new files
		$(document).on('DOMNodeInserted', function (e) {
			if ($(e.target).hasClass('attachment')) {
				setTimeout(function () {
					enhanceSingleMediaItem($(e.target));
				}, 500);
			}
		});
	});

	/**
	 * Enhance all media library items with tag management.
	 */
	function enhanceMediaLibraryItems() {
		// Grid view items
		$('.attachment').each(function () {
			enhanceSingleMediaItem($(this));
		});
	}

	/**
	 * Add tag management interface to a single media item.
	 *
	 * @param {jQuery} $item The media item element.
	 */
	function enhanceSingleMediaItem($item) {
		// Skip if already enhanced
		if ($item.find('.media-tags-container').length) {
			return;
		}

		// Check if cmeMediaTags is defined
		if (typeof cmeMediaTags === 'undefined') {
			if (typeof wp !== 'undefined' && wp.debug) {
				wp.debug.error('cmeMediaTags is not defined');
			}
			return;
		}

		// Get attachment ID after validation checks
		const attachmentId = $item.data('id');

		// Get the current tags for this attachment through WP REST API
		$.get(
			wp.ajax.settings.url,
			{
				action: 'get_attachment_terms',
				attachment_id: attachmentId,
				taxonomy: 'media_tag',
				nonce: cmeMediaTags.nonce,
			},
			function (response) {
				if (!response.success) {
					if (typeof wp !== 'undefined' && wp.debug) {
						wp.debug.error('Failed to get tags', response);
					}
					return;
				}

				// Prepare tags display and values
				if (response.data && response.data.terms) {
					// Initialize variables only when we have terms to process
					let tagsHtml = '';
					const tagValues = [];

					response.data.terms.forEach(function (term) {
						tagsHtml += `<span class="media-tag" data-term-id="${term.term_id}">${term.name}<span class="media-tag-remove dashicons dashicons-no-alt"></span></span>`;
						tagValues.push(term.name);
					});

					// Create the tags container
					const $container = $(
						'<div class="media-tags-container"></div>'
					);
					const $tagsDisplay = $(
						'<div class="media-tags-display"></div>'
					).html(tagsHtml);
					const $input = $(
						'<input type="text" class="media-tags-input" placeholder="Add tags..." value="" />'
					);
					const $actions = $('<div class="media-tag-actions"></div>');
					const $save = $(
						'<button class="button button-small media-tag-save">Save</button>'
					);

					// Set the current tags as input value
					$input.val(tagValues.join(', '));

					// Add the elements to the container
					$actions.append($save);
					$container
						.append($tagsDisplay)
						.append($input)
						.append($actions);

					// Add the container to the media item
					if ($item.find('.attachment-preview').length) {
						$item.find('.attachment-preview').append($container); // Grid view
					} else {
						$item.find('.media-icon').after($container); // List view
					}
					// Handle tag save button click
					$save.on('click', function (e) {
						e.preventDefault();
						saveMediaTags(attachmentId, $input.val(), $item);
					});

					// Handle enter key press in the input
					$input.on('keypress', function (e) {
						if (e.which === 13) {
							e.preventDefault();
							saveMediaTags(attachmentId, $input.val(), $item);
						}
					});

					// Handle click on tag to remove
					$tagsDisplay.on('click', '.media-tag-remove', function () {
						const $tag = $(this).parent();
						const tagName = $tag.text().replace('×', '').trim();
						// Get and process current tags only when needed
						const currentTags = $input
							.val()
							.split(',')
							.map((tag) => tag.trim());

						const updatedTags = currentTags
							.filter((tag) => tag !== tagName)
							.join(', ');
						$input.val(updatedTags);
						$tag.remove();
					});
				}
			}
		);
	}

	/**
	 * Save the media tags for an attachment.
	 *
	 * @param {number} attachmentId The attachment ID.
	 * @param {string} tags         The comma-separated list of tags.
	 * @param {jQuery} $item        The media item element.
	 */
	function saveMediaTags(attachmentId, tags, $item) {
		// Check if cmeMediaTags is defined
		if (typeof cmeMediaTags === 'undefined') {
			if (typeof wp !== 'undefined' && wp.debug) {
				wp.debug.error('cmeMediaTags is not defined');
			}
			return;
		}

		$.post(
			cmeMediaTags.ajaxUrl,
			{
				action: 'update_media_tags',
				attachment_id: attachmentId,
				tags,
				nonce: cmeMediaTags.nonce,
			},
			function (response) {
				if (response.success) {
					// Update the tags display
					if (response.data && response.data.tags) {
						// Initialize variables only when we have tags to process
						let tagsHtml = '';
						const tagValues = [];

						response.data.tags.forEach(function (term) {
							tagsHtml += `<span class="media-tag" data-term-id="${term.term_id}">${term.name}<span class="media-tag-remove dashicons dashicons-no-alt"></span></span>`;
							tagValues.push(term.name);
						});

						// Update the HTML display with new tags
						$item.find('.media-tags-display').html(tagsHtml);
						$item
							.find('.media-tags-input')
							.val(tagValues.join(', '));
					} else {
						$item.find('.media-tags-display').html('');
						$item.find('.media-tags-input').val('');
					}

					// Show success feedback
					const $feedback = $(
						'<div class="updated notice is-dismissible"><p>Tags updated successfully!</p></div>'
					);
					$('#wpbody-content').prepend($feedback);

					// Remove feedback after 2 seconds
					setTimeout(function () {
						$feedback.fadeOut(function () {
							$(this).remove();
						});
					}, 2000);
				} else if (typeof wp !== 'undefined' && wp.debug) {
					wp.debug.error('Failed to update tags', response);
				}
			}
		);
	}
})(jQuery);
