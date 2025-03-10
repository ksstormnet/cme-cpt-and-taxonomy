<?php
/**
 * Admin enhancements for Media Library.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * Admin class.
 *
 * Enhances the admin interface for media tags.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 * @author     Your Name
 */
class Admin {

	/**
	 * Initialize the class.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register(): void {
		// Enqueue scripts and styles for admin.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ) ];

		// Add AJAX handlers for tag management.
		add_action( 'wp_ajax_update_media_tags', [ $this, 'ajax_update_media_tags' ) ];

		// Add filter to Media Library
		add_action( 'restrict_manage_posts', [ $this, 'add_media_tags_filter' ) ];

		// Filter attachments by custom taxonomy
		add_filter( 'parse_query', [ $this, 'filter_attachments_by_taxonomy' ) ];
	}

	/**
	 * Enqueue scripts and styles for admin.
	 *
	 * @since    1.0.0
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void {
		// Only enqueue on media library pages.
		if ( 'upload.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'cme-media-tags-admin',
			CME_PLUGIN_URL . 'assets/css/admin.css',
			[],
			CME_VERSION
		);

		wp_enqueue_script(
			'cme-media-tags-admin',
			CME_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery', 'wp-util' ],
			CME_VERSION,
			true
		);

		wp_localize_script(
			'cme-media-tags-admin',
			'cmeMediaTags',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'media-tags-nonce' ),
			)
		);
	}

	/**
	 * AJAX handler for updating media tags.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function ajax_update_media_tags(): void {
		// Check nonce for security.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'media-tags-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions.
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		// Get attachment ID.
		$attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;
		if ( ! $attachment_id ) {
			wp_send_json_error( 'Invalid attachment ID' );
		}

		// Get tags.
		$tags = isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : '';

		// Update attachment tags.
		$tag_array = array_map( 'trim', explode( ',', $tags ) );
		$tag_array = array_filter( $tag_array ); // Remove empty values
		wp_set_object_terms( $attachment_id, $tag_array, 'media_tag', false );

		// Return success.
		wp_send_json_success(
			[
				'message' => 'Tags updated successfully',
				'tags'    => get_the_terms( $attachment_id, 'media_tag' ),
			)
		);
	}

	/**
	 * Add media tags filter to Media Library.
	 *
	 * @since    1.0.0
	 * @param string $post_type The current post type.
	 * @return void
	 */
	public function add_media_tags_filter( string $post_type ): void {
		if ( 'attachment' !== $post_type ) {
			return;
		}

		$taxonomy = 'media_tag';
		$tax      = get_taxonomy( $taxonomy );
		if ( ! $tax ) {
			return;
		}

		$selected = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
		wp_dropdown_categories(
			[
				'show_option_all' => sprintf( __( 'All %s', 'cme-cpt-and-taxonomy' ), $tax->labels->name ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'hierarchical'    => false,
				'show_count'      => true,
				'hide_empty'      => true,
			)
		);
	}

	/**
	 * Filter attachments by custom taxonomy.
	 *
	 * @since    1.0.0
	 * @param \WP_Query $query The WordPress query object.
	 * @return void
	 */
	public function filter_attachments_by_taxonomy( \WP_Query $query ): void {
		global $pagenow;

		$taxonomy = 'media_tag';

		// Only filter in admin media library
		if ( ! is_admin() || 'upload.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}

		// Check if we're filtering by taxonomy
		if ( isset( $_GET[ $taxonomy ] ) && ! empty( $_GET[ $taxonomy ] ) && is_numeric( $_GET[ $taxonomy ] ) ) {
			$term_id = intval( $_GET[ $taxonomy ] );
			$term    = get_term( $term_id, $taxonomy );

			if ( $term && ! is_wp_error( $term ) ) {
				$query->set(
					'tax_query',
					[
						[
							'taxonomy' => $taxonomy,
							'field'    => 'id',
							'terms'    => [ $term_id ],
						),
					)
				);
			}
		}
	}
}
