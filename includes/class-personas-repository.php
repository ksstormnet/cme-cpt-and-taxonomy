<?php
/**
 * Personas Repository Class
 *
 * Handles retrieval of persona data from the database.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Repository Class
 *
 * This class is responsible for retrieving persona data from the database,
 * including available personas, persona details, and post IDs.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Repository {

	/**
	 * Instance of the class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Repository    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Cached list of all personas.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      array    $personas_cache    Cached list of all personas.
	 */
	private $personas_cache = null;

	/**
	 * Cached persona details.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      array    $details_cache    Cached persona details.
	 */
	private $details_cache = array();

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.6.0
	 * @return    Personas_Repository    The singleton instance.
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
		// Add hook to reset cache when a persona post is saved or deleted.
		add_action( 'save_post_persona', array( $this, 'reset_cache' ) );
		add_action( 'delete_post', array( $this, 'reset_cache_on_delete' ) );
	}

	/**
	 * Get all available personas.
	 *
	 * @since     1.6.0
	 * @return    array    Array of available personas in format [id => name].
	 */
	public function get_all_personas() {
		// Return cached results if available.
		if ( null !== $this->personas_cache ) {
			return $this->personas_cache;
		}

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
			$key              = sanitize_title( $persona->post_title );
			$personas[ $key ] = $persona->post_title;
		}

		// Allow filtering.
		$this->personas_cache = apply_filters( 'cme_personas_available', $personas );

		return $this->personas_cache;
	}

	/**
	 * Get the post ID for a persona by slug or title.
	 *
	 * @since     1.6.0
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
	 * @since     1.6.0
	 * @param     string $persona_id    The persona identifier.
	 * @return    array|null            The persona details, or null if not found.
	 */
	public function get_persona_details( $persona_id ) {
		// Return cached results if available.
		if ( isset( $this->details_cache[ $persona_id ] ) ) {
			return $this->details_cache[ $persona_id ];
		}

		if ( 'default' === $persona_id ) {
			$details = array(
				'id'      => 'default',
				'title'   => __( 'Default', 'cme-personas' ),
				'slug'    => 'default',
				'post_id' => null,
			);

			$this->details_cache[ $persona_id ] = $details;
			return $details;
		}

		$post_id = $this->get_persona_post_id( $persona_id );
		if ( ! $post_id ) {
			return null;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$details = array(
			'id'      => $persona_id,
			'title'   => $post->post_title,
			'slug'    => $post->post_name,
			'post_id' => $post_id,
			'excerpt' => $post->post_excerpt,
		);

		// Cache the details.
		$this->details_cache[ $persona_id ] = $details;

		return $details;
	}

	/**
	 * Reset the cache when a persona post is saved.
	 *
	 * @since     1.6.0
	 * @return    void
	 */
	public function reset_cache() {
		$this->personas_cache = null;
		$this->details_cache  = array();
	}

	/**
	 * Reset the cache when a persona post is deleted.
	 *
	 * @since     1.6.0
	 * @param     int $post_id    The post ID being deleted.
	 * @return    void
	 */
	public function reset_cache_on_delete( $post_id ) {
		if ( 'persona' === get_post_type( $post_id ) ) {
			$this->reset_cache();
		}
	}

	/**
	 * Get all persona post IDs.
	 *
	 * @since     1.6.0
	 * @return    array    Array of post IDs.
	 */
	public function get_all_persona_post_ids() {
		$posts = get_posts(
			array(
				'post_type'      => 'persona',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		return $posts;
	}
}
