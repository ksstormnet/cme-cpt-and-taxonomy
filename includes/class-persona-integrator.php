<?php
/**
 * Persona Integrator Class
 *
 * Handles integration of persona functionality into the WordPress system.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Persona Integrator Class
 *
 * This class integrates all persona functionality into the WordPress system.
 * It initializes the core classes and hooks them into the WordPress lifecycle.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Persona_Integrator {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Integrator    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance of the Persona_Manager class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Manager    $persona_manager    Instance of the Persona_Manager class.
	 */
	private $persona_manager;

	/**
	 * Instance of the Personas_API class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Personas_API    $personas_api    Instance of the Personas_API class.
	 */
	private $personas_api;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Persona_Integrator    The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since    1.1.0
	 */
	private function __construct() {
		// Include required files.
		$this->includes();

		// Initialize instances.
		$this->persona_manager = Persona_Manager::get_instance();
		$this->personas_api    = Personas_API::get_instance();

		// Initialize handlers.
		Ajax_Handler::get_instance();
		Frontend::get_instance();

		// Set up hooks.
		$this->setup_hooks();

		// Set up global functions.
		$this->setup_global_functions();
	}

	/**
	 * Include required files.
	 *
	 * @since    1.1.0
	 */
	private function includes() {
		// Core classes - only include if they're not already included by the autoloader.
		$base_path = plugin_dir_path( __DIR__ ) . 'includes/';

		if ( ! class_exists( '\\CME_Personas\\Persona_Manager' ) ) {
			require_once $base_path . 'class-persona-manager.php';
		}

		if ( ! class_exists( '\\CME_Personas\\Personas_API' ) ) {
			require_once $base_path . 'class-personas-api.php';
		}

		if ( ! class_exists( '\\CME_Personas\\Ajax_Handler' ) ) {
			require_once $base_path . 'class-ajax-handler.php';
		}

		if ( ! class_exists( '\\CME_Personas\\Frontend' ) ) {
			require_once $base_path . 'class-frontend.php';
		}
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.1.0
	 */
	private function setup_hooks() {
		// Hook into activation.
		register_activation_hook( \CME_PERSONAS_FILE, array( $this, 'activate' ) );

		// Hook into deactivation.
		register_deactivation_hook( \CME_PERSONAS_FILE, array( $this, 'deactivate' ) );

		// Hook into plugin uninstall.
		register_uninstall_hook( \CME_PERSONAS_FILE, array( __CLASS__, 'uninstall' ) );

		// Hook into admin.
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Set up global functions.
	 *
	 * @since    1.1.0
	 */
	private function setup_global_functions() {
		if ( ! function_exists( 'cme_get_current_persona' ) ) {
			/**
			 * Get the current persona.
			 *
			 * @since     1.1.0
			 * @return    string    The current persona identifier.
			 */
			function cme_get_current_persona() {
				$api = Personas_API::get_instance();
				return $api->get_current_persona();
			}
		}

		if ( ! function_exists( 'cme_set_persona' ) ) {
			/**
			 * Set the active persona.
			 *
			 * @since     1.1.0
			 * @param     string $persona_id    The persona identifier to set.
			 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
			 * @return    bool                  Whether the persona was set successfully.
			 */
			function cme_set_persona( $persona_id, $set_cookie = true ) {
				$api = Personas_API::get_instance();
				return $api->set_persona( $persona_id, $set_cookie );
			}
		}

		if ( ! function_exists( 'cme_get_all_personas' ) ) {
			/**
			 * Get all available personas.
			 *
			 * @since     1.1.0
			 * @return    array    Array of available personas in format [id => name].
			 */
			function cme_get_all_personas() {
				$api = Personas_API::get_instance();
				return $api->get_all_personas();
			}
		}
	}

	/**
	 * Register assets.
	 *
	 * @since    1.1.0
	 */
	public function register_assets() {
		// Frontend CSS.
		wp_register_style(
			'cme-personas',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/css/personas-frontend.css',
			array(),
			\CME_PERSONAS_VERSION,
			'all'
		);

		// Frontend JS.
		wp_register_script(
			'cme-personas',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/js/personas-frontend.js',
			array( 'jquery' ),
			\CME_PERSONAS_VERSION,
			true
		);

		// Enqueue frontend assets.
		wp_enqueue_style( 'cme-personas' );
		wp_enqueue_script( 'cme-personas' );

		// Pass data to script.
		wp_localize_script(
			'cme-personas',
			'cmePersonas',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'cme_personas_nonce' ),
				'currentPersona' => $this->persona_manager->get_current_persona(),
				'reloadOnSwitch' => apply_filters( 'cme_personas_reload_on_switch', false ),
			)
		);
	}

	/**
	 * Activation hook.
	 *
	 * @since    1.1.0
	 */
	public function activate() {
		// Nothing to do yet.
	}

	/**
	 * Deactivation hook.
	 *
	 * @since    1.1.0
	 */
	public function deactivate() {
		// Nothing to do yet.
	}

	/**
	 * Uninstall hook.
	 *
	 * @since    1.1.0
	 */
	public static function uninstall() {
		// Nothing to do yet.
	}

	/**
	 * Admin initialization.
	 *
	 * @since    1.1.0
	 */
	public function admin_init() {
		// Add admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Register admin assets.
	 *
	 * @since    1.1.0
	 */
	public function admin_assets() {
		// Get current screen.
		$screen = get_current_screen();

		// Admin CSS.
		wp_register_style(
			'cme-personas-admin',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'admin/css/personas-admin.css',
			array(),
			\CME_PERSONAS_VERSION,
			'all'
		);

		// Admin JS.
		wp_register_script(
			'cme-personas-admin',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'admin/js/personas-admin.js',
			array( 'jquery', 'jquery-ui-dialog' ),
			\CME_PERSONAS_VERSION,
			true
		);

		// Enqueue admin assets.
		wp_enqueue_style( 'cme-personas-admin' );
		wp_enqueue_script( 'cme-personas-admin' );

		// Enqueue jQuery UI styles for admin.
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		// Register and enqueue block editor integration for edit screens.
		if ( $screen && ( 'post' === $screen->base || 'page' === $screen->base ) && 'edit' === $screen->action ) {
			// Check if block editor is active.
			if ( function_exists( 'use_block_editor_for_post' ) && use_block_editor_for_post( get_the_ID() ) ) {
				$this->enqueue_block_editor_assets();
			}
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since    1.2.0
	 */
	private function enqueue_block_editor_assets() {
		// Block editor integration.
		wp_enqueue_script(
			'cme-personas-block-editor',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'admin/js/block-editor.js',
			array(
				'wp-plugins',
				'wp-edit-post',
				'wp-element',
				'wp-components',
				'wp-data',
				'wp-core-data',
				'wp-api-fetch',
				'wp-i18n',
				'cme-personas-admin',
			),
			\CME_PERSONAS_VERSION,
			true
		);

		// Pass data to script.
		wp_localize_script(
			'cme-personas-admin',
			'cmePersonasAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cme_personas_admin_nonce' ),
				'personas' => $this->persona_manager->get_all_personas(),
				'i18n'     => array(
					'closeButton'  => __( 'Close', 'cme-personas' ),
					/* translators: %s: Persona name */
					'previewBadge' => __( '%s Persona View', 'cme-personas' ),
				),
			)
		);
	}
}
