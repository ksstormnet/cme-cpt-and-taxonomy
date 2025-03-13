<?php
/**
 * Persona Manager Class
 *
 * Handles persona detection, context, and storage.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Persona Manager Class
 *
 * This class is responsible for detecting, retrieving, and managing persona context.
 * It provides methods for getting the current persona, switching personas,
 * and retrieving persona information.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Persona_Manager {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Manager    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Current active persona.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $current_persona    The current active persona identifier.
	 */
	private $current_persona = 'default';

	/**
	 * Cookie name for storing persona preference.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $cookie_name    The name of the cookie for storing persona preference.
	 */
	private $cookie_name = 'cme_persona';

	/**
	 * URL parameter name for specifying persona.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $url_param    The URL parameter name for specifying persona.
	 */
	private $url_param = 'persona';

	/**
	 * Cookie expiration time in days.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      int    $cookie_expiration    Cookie expiration time in days.
	 */
	private $cookie_expiration = 30;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Persona_Manager    The singleton instance.
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
		// Initialize hooks.
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'wp', array( $this, 'detect_persona' ) );
	}

	/**
	 * Initialize the class.
	 *
	 * @since    1.1.0
	 * @return   void
	 */
	public function initialize() {
		// Initialize any required settings or options.
		$this->cookie_name = apply_filters( 'cme_persona_cookie_name', $this->cookie_name );
		$this->url_param = apply_filters( 'cme_persona_url_param', $this->url_param );
		$this->cookie_expiration = apply_filters( 'cme_persona_cookie_expiration', $this->cookie_expiration );
	}

	/**
	 * Detect the current persona from various sources.
	 *
	 * Priority order:
	 * 1. URL parameter
	 * 2. Cookie
	 * 3. Default persona
	 *
	 * @since    1.1.0
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
				$this->set_persona( $persona );
				return;
			}
		}

		// Then check cookie.
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$persona = sanitize_text_field( wp_unslash( $_COOKIE[ $this->cookie_name ] ) );
			if ( $this->is_valid_persona( $persona ) ) {
				$this->current_persona = $persona;
				return;
			}
		}

		// Use default if not detected.
		$this->current_persona = 'default';
	}

	/**
	 * Check if a persona is valid.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier to check.
	 * @return    bool                  Whether the persona is valid.
	 */
	public function is_valid_persona( $persona_id ) {
		if ( 'default' === $persona_id ) {
			return true;
		}

		$personas = $this->get_all_personas();
		return isset( $personas[ $persona_id ] );
	}

	/**
	 * Get all available personas.
	 *
	 * @since     1.1.0
	 * @return    array    Array of available personas in format [id => name].
	 */
	public function get_all_personas() {
		// Start with the default persona.
		$personas = array(
			'default' => __( 'Default', 'cme-personas' ),
		);

		// Get all published persona posts.
		$persona_posts = get_posts(
			array(
				'post_type'      => 'persona',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		// Add each persona to the array.
		foreach ( $persona_posts as $persona ) {
			$key = sanitize_title( $persona->post_title );
			$personas[ $key ] = $persona->post_title;
		}

		// Allow filtering.
		return apply_filters( 'cme_personas_available', $personas );
	}

	/**
	 * Get the current persona.
	 *
	 * @since     1.1.0
	 * @return    string    The current persona identifier.
	 */
	public function get_current_persona() {
		return apply_filters( 'cme_current_persona', $this->current_persona );
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
		if ( ! $this->is_valid_persona( $persona_id ) ) {
			return false;
		}

		$this->current_persona = $persona_id;

		// Set cookie if requested and if not in admin.
		if ( $set_cookie && ! is_admin() ) {
			$this->set_persona_cookie( $persona_id );
		}

		// Allow other components to respond to the persona change.
		do_action( 'cme_persona_changed', $persona_id );

		return true;
	}

	/**
	 * Clear the persona setting.
	 *
	 * @since     1.1.0
	 * @return    void
	 */
	public function clear_persona() {
		$this->current_persona = 'default';

		// Clear the cookie.
		if ( isset( $_COOKIE[ $this->cookie_name ] ) ) {
			setcookie( $this->cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}

		// Allow other components to respond.
		do_action( 'cme_persona_cleared' );
	}

	/**
	 * Set the persona cookie.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier to store in the cookie.
	 * @return    void
	 */
	private function set_persona_cookie( $persona_id ) {
		$expiration = time() + ( $this->cookie_expiration * DAY_IN_SECONDS );
		setcookie( $this->cookie_name, $persona_id, $expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
	}

	/**
	 * Get the post ID for a persona by slug or title.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier (slug or title).
	 * @return    int|null              The post ID of the persona, or null if not found.
	 */
	public function get_persona_post_id( $persona_id ) {
		if ( 'default' === $persona_id ) {
			// Default persona doesn't have a post ID.
			return null;
		}

		// First try by slug.
		$posts = get_posts(
			array(
				'post_type'      => 'persona',
				'post_status'    => 'publish',
				'name'           => $persona_id,
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $posts ) ) {
			return $posts[0]->ID;
		}

		// Then try by title.
		$posts = get_posts(
			array(
				'post_type'      => 'persona',
				'post_status'    => 'publish',
				'title'          => $persona_id,
				'posts_per_page' => 1,
			)
		);

		if ( ! empty( $posts ) ) {
			return $posts[0]->ID;
		}

		// Not found.
		return null;
	}

	/**
	 * Get persona details.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier.
	 * @return    array|null            The persona details, or null if not found.
	 */
	public function get_persona_details( $persona_id ) {
		if ( 'default' === $persona_id ) {
			return array(
				'id'    => 'default',
				'title' => __( 'Default', 'cme-personas' ),
				'slug'  => 'default',
				'post_id' => null,
			);
		}

		$post_id = $this->get_persona_post_id( $persona_id );
		if ( ! $post_id ) {
			return null;
		}

		$post = get_post( $post_id );
		return array(
			'id'      => $persona_id,
			'title'   => $post->post_title,
			'slug'    => $post->post_name,
			'post_id' => $post_id,
			'excerpt' => $post->post_excerpt,
		);
	}
}
