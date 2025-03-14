<?php
/**
 * Personas Detector Class
 *
 * Handles persona detection from various sources.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Detector Class
 *
 * This class is responsible for detecting the current persona from various sources.
 * It follows a priority order: URL parameter, cookie, default.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Detector {

	/**
	 * Instance of the class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Detector    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Current active persona.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      string    $current_persona    The current active persona identifier.
	 */
	private $current_persona = 'default';

	/**
	 * URL parameter name for specifying persona.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      string    $url_param    The URL parameter name for specifying persona.
	 */
	private $url_param = 'persona';

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
	 * @return    Personas_Detector    The singleton instance.
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
		// Initialize dependencies.
		$this->repository = Personas_Repository::get_instance();
		$this->storage    = Personas_Storage::get_instance();

		// Initialize hooks.
		add_action( 'wp', array( $this, 'detect_persona' ) );

		// Initialize URL parameter.
		$this->url_param = apply_filters( 'cme_persona_url_param', $this->url_param );
	}

	/**
	 * Detect the current persona from various sources.
	 *
	 * Priority order:
	 * 1. URL parameter
	 * 2. Cookie
	 * 3. Default persona
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	public function detect_persona() {
		// Skip in admin.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// Check URL parameter first.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $this->url_param ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$persona = sanitize_text_field( wp_unslash( $_GET[ $this->url_param ] ) );
			if ( $this->is_valid_persona( $persona ) ) {
				$this->set_current_persona( $persona );
				$this->storage->set_persona_cookie( $persona );
				return;
			}
		}

		// Then check cookie.
		$cookie_persona = $this->storage->get_persona_from_cookie();
		if ( $cookie_persona && $this->is_valid_persona( $cookie_persona ) ) {
			$this->set_current_persona( $cookie_persona );
			return;
		}

		// Use default if not detected.
		$this->set_current_persona( 'default' );
	}

	/**
	 * Check if a persona is valid.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to check.
	 * @return    bool                  Whether the persona is valid.
	 */
	public function is_valid_persona( $persona_id ) {
		if ( 'default' === $persona_id ) {
			return true;
		}

		$personas = $this->repository->get_all_personas();
		return isset( $personas[ $persona_id ] );
	}

	/**
	 * Get the current persona.
	 *
	 * @since     1.6.0
	 * @return    string    The current persona identifier.
	 */
	public function get_current_persona() {
		return apply_filters( 'cme_current_persona', $this->current_persona );
	}

	/**
	 * Set the current persona.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to set.
	 * @return    void
	 */
	private function set_current_persona( $persona_id ) {
		$old_persona           = $this->current_persona;
		$this->current_persona = $persona_id;

		// Allow other components to respond to the persona change.
		if ( $old_persona !== $persona_id ) {
			do_action( 'cme_persona_changed', $persona_id, $old_persona );
		}
	}

	/**
	 * Determine if we are in a preview context.
	 *
	 * @since     1.6.0
	 * @return    bool    Whether we are in a preview context.
	 */
	public function is_preview() {
		// Check for preview parameter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET[ $this->url_param ] ) && is_admin();
	}

	/**
	 * Backward compatibility with Persona_Manager.
	 *
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier to set.
	 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
	 * @return    bool                  Whether the persona was set successfully.
	 */
	public function set_persona( $persona_id, $set_cookie = true ) {
		if ( ! $this->is_valid_persona( $persona_id ) ) {
			return false;
		}

		$this->set_current_persona( $persona_id );

		// Set cookie if requested.
		if ( $set_cookie ) {
			$this->storage->set_persona_cookie( $persona_id );
		}

		return true;
	}

	/**
	 * Clear the persona setting.
	 *
	 * @since     1.6.0
	 * @return    void
	 */
	public function clear_persona() {
		$this->set_current_persona( 'default' );
		$this->storage->clear_persona_cookie();

		// Allow other components to respond.
		do_action( 'cme_persona_cleared' );
	}
}
