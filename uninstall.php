<?php
/**
 * Uninstall procedures.
 *
 * @package CME_Personas
 */

// If uninstall.php is not called by WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options created by the plugin.
delete_option( 'cme_personas_version' );
delete_option( 'cme_personas_welcome_shown' );

// Clean up any transients we've created.
delete_transient( 'cme_personas_flush_rewrite' );

// Note: We're not deleting posts or terms as that could result in data loss.
// If you want to clean all data on uninstall, uncomment these lines below.

/**
 * Uncomment this code to delete all plugin data on uninstall.
 * Warning: This will permanently delete all persona posts and media tags.
 *
 * // Get post types.
 * $post_types = ['persona'];
 *
 * // Delete all posts for the custom post types.
 * foreach ($post_types as $post_type) {
 *     $posts = get_posts([
 *         'post_type'      => $post_type,
 *         'posts_per_page' => -1,
 *         'post_status'    => 'any',
 *     ]);
 *
 *     foreach ($posts as $post) {
 *         wp_delete_post($post->ID, true);
 *     }
 * }
 *
 * // Delete the taxonomy.
 * $terms = get_terms([
 *     'taxonomy'   => 'media_tag',
 *     'hide_empty' => false,
 * ]);
 *
 * foreach ($terms as $term) {
 *     wp_delete_term($term->term_id, 'media_tag');
 * }
 */
