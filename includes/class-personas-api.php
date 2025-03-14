<?php
/**
 * Personas API Class
 *
 * Provides public API functions for the Personas system.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas API Class
 *
 * This class provides public functions for interacting with the Personas system.
 * It acts as a facade for the Persona_Manager class,
 * offering simple functions that can be used in templates and other plugins.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Personas_API {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Personas_API    $instance    Singleton instance of the class.
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
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Personas_API    The singleton instance.
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
		$this->persona_manager = Persona_Manager::get_instance();
	}

	/**
	 * Register shortcodes.
	 *
	 * @since    1.1.0
	 * @return   void
	 * @deprecated 1.4.2 Shortcodes are now handled by Frontend class
	 */
	public function register_shortcodes() {
		// This method is kept for backward compatibility but does nothing.
		// Removed: add_shortcode calls.
	}

	/**
	 * Shortcode for persona switcher.
	 *
	 * Usage: [persona_switcher]
	 *
	 * @since     1.1.0
	 * @param     array $atts    Shortcode attributes.
	 * @return    string         The persona switcher HTML.
	 * @deprecated 1.4.2 Use the Frontend class implementation instead
	 */
	public function persona_switcher_shortcode( $atts ) {
		// Forward to the Frontend class implementation.
		$frontend = Frontend::get_instance();
		return $frontend->persona_switcher_shortcode( $atts );
	}

	/**
	 * Get the current persona.
	 *
	 * @since     1.1.0
	 * @return    string    The current persona identifier.
	 */
	public function get_current_persona() {
		return $this->persona_manager->get_current_persona();
	}

	/**
	 * Set the active persona.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier to set.
	 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
	 * @return    bool                  Whether the persona was set successfully.
	 */
	public function set_persona( $persona_id, $set_cookie = true ) {
		return $this->persona_manager->set_persona( $persona_id, $set_cookie );
	}

	/**
	 * Check if a persona is valid.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier to check.
	 * @return    bool                  Whether the persona is valid.
	 */
	public function is_valid_persona( $persona_id ) {
		return $this->persona_manager->is_valid_persona( $persona_id );
	}

	/**
	 * Get all available personas.
	 *
	 * @since     1.1.0
	 * @return    array    Array of available personas in format [id => name].
	 */
	public function get_all_personas() {
		return $this->persona_manager->get_all_personas();
	}

	/**
	 * Get persona details.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier.
	 * @return    array|null            The persona details, or null if not found.
	 */
	public function get_persona_details( $persona_id ) {
		return $this->persona_manager->get_persona_details( $persona_id );
	}
}
