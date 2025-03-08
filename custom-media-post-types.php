<?php
/**
 * Cruise Made Easy Post Types and Media Tags.
 *
 * @package     CME_CPT_Taxonomy
 * @author      Your Name
 * @copyright   2025 Your Name or Company
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Cruise Made Easy Post Types and Media Tags.
 * Plugin URI: https://example.com/plugin.
 * Description: Adds custom post types and a tag taxonomy for media library.
 * Version: 1.0.0.
 * Author: Your Name.
 * Author URI: https://example.com.
 * Text Domain: cme-cpt-and-taxonomy.
 * License: GPL v2 or later.
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt.
 * Requires at least: 6.4.
 * Requires PHP: 8.2.
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check WordPress version
global $wp_version;
if ( version_compare( $wp_version, '6.4', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Cruise Made Easy Post Types and Media Tags requires WordPress version 6.4 or higher.', 'cme-cpt-and-taxonomy' ); ?></p>
		</div>
			<?php
		}
	);
	return;
}

// Check PHP version
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Cruise Made Easy Post Types and Media Tags requires PHP version 8.2 or higher.', 'cme-cpt-and-taxonomy' ); ?></p>
		</div>
			<?php
		}
	);
	return;
}

/**
 * Define plugin constants.
 */
define( 'CME_VERSION', '1.0.0' );
define( 'CME_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CME_PLUGIN_FILE', __FILE__ );

/**
 * The core plugin class.
 */
require_once CME_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_cme_cpt_taxonomy() {
	$plugin = new CME_CPT_Taxonomy\Plugin();
	$plugin->run();
}
run_cme_cpt_taxonomy();

// Add activation hook to flush rewrite rules
register_activation_hook(
	CME_PLUGIN_FILE,
	function () {
		// Save plugin version
		update_option( 'cme_cpt_taxonomy_version', CME_VERSION );

		// Schedule a rewrite flush
		set_transient( 'cme_cpt_taxonomy_flush_rewrite', 1, 30 );
	}
);

// Flush rewrites when needed
add_action(
	'admin_init',
	function () {
		if ( get_transient( 'cme_cpt_taxonomy_flush_rewrite' ) ) {
			delete_transient( 'cme_cpt_taxonomy_flush_rewrite' );
			flush_rewrite_rules();
		}
	}
);

// Add plugin action links for easier access to settings
add_filter(
	'plugin_action_links_' . plugin_basename( CME_PLUGIN_FILE ),
	function ( $links ) {
		// Add custom action links
		$custom_links = array(
			'<a href="' . admin_url( 'edit.php?post_type=persona' ) . '">' . __( 'Personas', 'cme-cpt-and-taxonomy' ) . '</a>',
			'<a href="' . admin_url( 'edit-tags.php?taxonomy=media_tag&post_type=attachment' ) . '">' . __( 'Media Tags', 'cme-cpt-and-taxonomy' ) . '</a>',
		);

		return array_merge( $custom_links, $links );
	}
);

// Create plugin placeholder directory for images
register_activation_hook(
	CME_PLUGIN_FILE,
	function () {
		// Create placeholder directory for images
		$upload_dir      = wp_upload_dir();
		$placeholder_dir = $upload_dir['basedir'] . '/cme-placeholders';

		if ( ! file_exists( $placeholder_dir ) ) {
			wp_mkdir_p( $placeholder_dir );
		}
	}
);
