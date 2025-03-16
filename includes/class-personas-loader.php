<?php
/**
 * Personas Loader Class
 *
 * Bootstrap class for initializing the plugin components.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Loader Class
 *
 * This class is responsible for initializing all plugin components,
 * loading required files, and setting up the plugin's infrastructure.
 * It's the main bootstrap mechanism for the plugin.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Loader {

	/**
	 * Initialize and run the plugin.
	 *
	 * @since    1.6.0
	 */
	public function run() {
		// Load dependencies.
		$this->load_dependencies();

		// Load text domain for internationalization.
		$this->load_textdomain();

		// Initialize core components.
		$this->initialize_components();

		// Set up WordPress hooks and actions.
		$this->setup_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function load_dependencies() {
		// Core components.
		$base_path = plugin_dir_path( __DIR__ ) . 'includes/';

		// Load component files.
		$files = array(
			'class-personas-post-types.php',
			'class-personas-admin.php',
			'class-personas-dashboard.php',
			'class-personas-repository.php',
			'class-personas-storage.php',
			'class-personas-detector.php',
			'class-personas-facade.php',
			'class-personas-assets.php',
			'class-personas-shortcodes.php',
			'class-personas-settings.php',
			'class-personas-frontend.php',
		);

		foreach ( $files as $file ) {
			if ( file_exists( $base_path . $file ) ) {
				require_once $base_path . $file;
			}
		}
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function initialize_components() {
		// Initialize Custom Post Types.
		$post_types = new Personas_Post_Types();
		$post_types->register();

		// Initialize admin components.
		$admin = new Personas_Admin();
		$admin->register();

		// Initialize shortcodes.
		$shortcodes = new Personas_Shortcodes();
		$shortcodes->register();

		// Initialize settings.
		$settings = new Personas_Settings();
		$settings->register();

		// Initialize components that use get_instance().
		Personas_Dashboard::get_instance();
		Personas_Repository::get_instance();
		Personas_Storage::get_instance();
		Personas_Detector::get_instance();
		Personas_Facade::get_instance();
		Personas_Assets::get_instance();
		Personas_Frontend::get_instance();
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function setup_hooks() {
		// No hooks needed after removing welcome notice.
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'cme-personas',
			false,
			dirname( plugin_basename( CME_PERSONAS_FILE ) ) . '/languages/'
		);
	}
}
