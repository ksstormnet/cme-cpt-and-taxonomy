<?php
/**
 * Personas Facade Class
 *
 * Provides a simplified public API for the Personas system.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Facade Class
 *
 * This class provides a simplified public API (facade) for interacting with
 * the Personas system. It encapsulates the complexity of the underlying
 * components and provides a clean interface for templates and other plugins.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Facade {

	/**
	 * Instance of the class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Facade    $instance    Singleton instance of the class.
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
	 * Instance of the Personas_Repository class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Repository    $repository    Instance of the Personas_Repository class.
	 */
	private $repository;

	/**
	 * Instance of the Personas_Storage class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Storage    $storage    Instance of the Personas_Storage class.
	 */
	private $storage;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.6.0
	 * @return    Personas_Facade    The singleton instance.
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
		$this->detector   = Personas_Detector::get_instance();
		$this->repository = Personas_Repository::get_instance();
		$this->storage    = Personas_Storage::get_instance();
	}

	/**
	 * Register shortcodes. For backward compatibility only.
	 *
	 * @since    1.6.0
	 * @return   void
	 * @deprecated 1.4.2 Shortcodes are now handled by Frontend class
	 */
	public function register_shortcodes() {
		// This method is kept for backward compatibility but does nothing.
		// See Frontend class for shortcode implementation.
	}

	/**
	 * Get the current persona.
	 *
	 * @since     1.6.0
	 * @return    string    The current persona identifier.
	 */
	public function get_current_persona() {
		return $this->detector->get_current_persona();
	}

	/**
	 * Set the active persona.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to set.
	 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
	 * @return    bool                  Whether the persona was set successfully.
	 */
	public function set_persona( $persona_id, $set_cookie = true ) {
		return $this->detector->set_persona( $persona_id, $set_cookie );
	}

	/**
	 * Clear the persona setting.
	 *
	 * @since     1.6.0
	 * @return    void
	 */
	public function clear_persona() {
		$this->detector->clear_persona();
	}

	/**
	 * Check if a persona is valid.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to check.
	 * @return    bool                  Whether the persona is valid.
	 */
	public function is_valid_persona( $persona_id ) {
		return $this->detector->is_valid_persona( $persona_id );
	}

	/**
	 * Get all available personas.
	 *
	 * @since     1.6.0
	 * @return    array    Array of available personas in format [id => name].
	 */
	public function get_all_personas() {
		return $this->repository->get_all_personas();
	}

	/**
	 * Get persona details.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier.
	 * @return    array|null            The persona details, or null if not found.
	 */
	public function get_persona_details( $persona_id ) {
		return $this->repository->get_persona_details( $persona_id );
	}

	/**
	 * Get the post ID for a persona by slug or title.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier (slug or title).
	 * @return    int|null              The post ID of the persona, or null if not found.
	 */
	public function get_persona_post_id( $persona_id ) {
		return $this->repository->get_persona_post_id( $persona_id );
	}

	/**
	 * Shortcode for persona switcher. For backward compatibility only.
	 *
	 * @since     1.6.0
	 * @param     array $atts    Shortcode attributes.
	 * @return    string         The persona switcher HTML.
	 * @deprecated 1.4.2 Use the Frontend class implementation instead
	 */
	public function persona_switcher_shortcode( $atts ) {
		// Forward to the Frontend class implementation.
		$frontend = Frontend::get_instance();
		return $frontend->persona_switcher_shortcode( $atts );
	}
}
