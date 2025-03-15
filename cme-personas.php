<?php
/**
 * CME Personas
 *
 * @package           CME_Personas
 * @author            Cruise Made Easy
 * @copyright         2025 Cruise Made Easy
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Cruise Made Easy - Personas
 * Plugin URI:        https://cruisemadeeasy.com/plugins/cme-personas
 * Description:       Customer persona management system with content personalization.
 * Version:           1.6.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Cruise Made Easy
 * Author URI:        https://cruisemadeeasy.com/
 * Text Domain:       cme-personas
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'CME_PERSONAS_VERSION', '1.6.0' );
define( 'CME_PERSONAS_FILE', __FILE__ );
define( 'CME_PERSONAS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CME_PERSONAS_URL', plugin_dir_url( __FILE__ ) );

// Load the autoloader if it exists.
$autoloader_path = CME_PERSONAS_PATH . 'vendor/autoload.php';
if ( file_exists( $autoloader_path ) ) {
	require_once $autoloader_path;
}

// Define plugin constants for legacy compatibility.
define( 'CME_PLUGIN_DIR', CME_PERSONAS_PATH );
define( 'CME_PLUGIN_FILE', CME_PERSONAS_FILE );

// Include the main loader class for plugin initialization.
require_once CME_PERSONAS_PATH . 'includes/class-personas-loader.php';

/**
 * Initialize the plugin.
 */
function cme_personas_init() {
	// Initialize the plugin through the loader.
	$loader = new \CME_Personas\Personas_Loader();
	$loader->run();
}

// Start the plugin when WordPress is ready.
add_action( 'plugins_loaded', 'cme_personas_init' );
