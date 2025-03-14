<?php
/**
 * Personas AJAX Handler Class
 *
 * Handles AJAX requests for Persona functionality.
 *
 * @since      1.2.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas AJAX Handler Class
 *
 * This class handles all AJAX requests for the Persona system.
 *
 * @since      1.2.0
 * @package    CME_Personas
 */
class Personas_Ajax {

	/**
	 * Instance of the class.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      Personas_Ajax    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.2.0
	 * @return    Personas_Ajax    The singleton instance.
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
	 * @since    1.2.0
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.2.0
	 */
	private function setup_hooks() {
		// No AJAX handlers needed after removing entity-based content.
	}
}
