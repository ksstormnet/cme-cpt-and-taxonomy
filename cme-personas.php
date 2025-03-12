<?php
/**
 * Cruise Made Easy - Personas
 *
 * @package     CME_Personas
 * @author      Sky+Sea LLC d/b/a KSstorm Media
 * @copyright   2025 Sky+Sea LLC d/b/a KSstorm Media
 * @license     Proprietary
 *
 * @wordpress-plugin
 * Plugin Name: Cruise Made Easy - Personas
 * Plugin URI: https://example.com/plugin.
 * Description: Manages customer personas for personalized content delivery in cruise websites, enabling targeted experiences based on visitor preferences and travel styles.
 * Version: 1.1.0.
 * Author: Sky+Sea LLC d/b/a KSstorm Media.
 * Author URI: https://ksstorm.com.
 * Text Domain: cme-personas.
 * License: Proprietary.
 * License URI: All rights reserved.
 * Requires at least: 6.7.2.
 * Requires PHP: 8.3.
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check WordPress version.
global $wp_version;
if ( version_compare( $wp_version, '6.7.2', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'CME Personas requires WordPress version 6.7.2 or higher.', 'cme-personas' ); ?></p>
		</div>
			<?php
		}
	);
	return;
}

// Check PHP version.
if ( version_compare( PHP_VERSION, '8.3', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'CME Personas requires PHP version 8.3 or higher.', 'cme-personas' ); ?></p>
		</div>
			<?php
		}
	);
	return;
}

/**
 * Define plugin constants.
 */
define( 'CME_VERSION', '1.1.0' );
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
function cme_run_cme_personas() {
	$plugin = new CME_Personas\Plugin();
	$plugin->run();
}
cme_run_cme_personas();

// Add activation hook to flush rewrite rules.
register_activation_hook(
	CME_PLUGIN_FILE,
	function () {
		// Save plugin version.
		update_option( 'cme_personas_version', CME_VERSION );

		// Schedule a rewrite flush.
		set_transient( 'cme_personas_flush_rewrite', 1, 30 );
	}
);

// Flush rewrites when needed.
add_action(
	'admin_init',
	function () {
		if ( get_transient( 'cme_personas_flush_rewrite' ) ) {
			delete_transient( 'cme_personas_flush_rewrite' );
			flush_rewrite_rules();
		}
	}
);

// Add plugin action links for easier access to settings.
add_filter(
	'plugin_action_links_' . plugin_basename( CME_PLUGIN_FILE ),
	function ( $links ) {
		// Add custom action links.
		$custom_links = array(
			'<a href="' . admin_url( 'edit.php?post_type=persona' ) . '">' . __( 'Personas', 'cme-personas' ) . '</a>',
		);

		return array_merge( $custom_links, $links );
	}
);

// Create plugin placeholder directory for images.
register_activation_hook(
	CME_PLUGIN_FILE,
	function () {
		// Create placeholder directory for images.
		$upload_dir      = wp_upload_dir();
		$placeholder_dir = $upload_dir['basedir'] . '/cme-placeholders';

		if ( ! file_exists( $placeholder_dir ) ) {
			wp_mkdir_p( $placeholder_dir );
		}
	}
);
