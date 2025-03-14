<?php
/**
 * Personas Assets Class
 *
 * Handles registration and enqueuing of assets for the Personas system.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Assets Class
 *
 * This class is responsible for registering and enqueuing assets
 * for the Personas system, including scripts, styles, and block editor integration.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Assets {

	/**
	 * Instance of the class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Assets    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance of the Personas_Detector class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Detector    $detector    Instance of the Personas_Detector class.
	 */
	private $detector;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.6.0
	 * @return    Personas_Assets    The singleton instance.
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
	 * @since    1.6.0
	 */
	private function __construct() {
		$this->detector = Personas_Detector::get_instance();

		// Register frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_assets' ) );

		// Register admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
	}

	/**
	 * Register frontend assets.
	 *
	 * @since    1.6.0
	 */
	public function register_frontend_assets() {
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
				'currentPersona' => $this->detector->get_current_persona(),
				'reloadOnSwitch' => apply_filters( 'cme_personas_reload_on_switch', false ),
			)
		);
	}

	/**
	 * Register admin assets.
	 *
	 * @since    1.6.0
	 */
	public function register_admin_assets() {
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
	 * @since    1.6.0
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

		// Get repository instance.
		$repository = Personas_Repository::get_instance();

		// Pass data to script.
		wp_localize_script(
			'cme-personas-admin',
			'cmePersonasAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cme_personas_admin_nonce' ),
				'personas' => $repository->get_all_personas(),
				'i18n'     => array(
					'closeButton'  => __( 'Close', 'cme-personas' ),
					/* translators: %s: Persona name */
					'previewBadge' => __( '%s Persona View', 'cme-personas' ),
				),
			)
		);
	}
}
