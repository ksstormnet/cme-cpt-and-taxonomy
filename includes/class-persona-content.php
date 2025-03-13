<?php
/**
 * Persona Content Class
 *
 * Handles persona-specific content storage and retrieval.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Persona Content Class
 *
 * This class is responsible for storing and retrieving persona-specific content.
 * It provides methods for getting and setting content variations for different personas.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Persona_Content {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Content    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Meta key prefix for storing persona content variations.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $meta_key_prefix    The meta key prefix.
	 */
	private $meta_key_prefix = '_persona_content_';

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Persona_Content    The singleton instance.
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

		// Filter content.
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
		add_filter( 'the_title', array( $this, 'filter_title' ), 10, 2 );
		add_filter( 'get_the_excerpt', array( $this, 'filter_excerpt' ), 10, 2 );
	}

	/**
	 * Initialize the class.
	 *
	 * @since    1.1.0
	 * @return   void
	 */
	public function initialize() {
		// Initialize any required settings or options.
		$this->meta_key_prefix = apply_filters( 'cme_persona_content_meta_prefix', $this->meta_key_prefix );
	}

	/**
	 * Get persona-specific content for an entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $entity_type     The entity type (default: 'post').
	 * @param     string $content_field   The content field name (default: 'content').
	 * @param     string $persona_id      The persona ID (null for current).
	 * @return    mixed                   The persona-specific content, or original content if not found.
	 */
	public function get_content( $entity_id, $entity_type = 'post', $content_field = 'content', $persona_id = null ) {
		// Get the current persona if none specified.
		if ( null === $persona_id ) {
			$persona_manager = Persona_Manager::get_instance();
			$persona_id = $persona_manager->get_current_persona();
		}

		// If it's the default persona, return the original content.
		if ( 'default' === $persona_id ) {
			return $this->get_original_content( $entity_id, $entity_type, $content_field );
		}

		// Get the persona-specific content from post meta.
		$variations = $this->get_content_variations( $entity_id, $entity_type, $persona_id );

		// Return the persona-specific content if it exists, otherwise the original.
		if ( isset( $variations[ $content_field ] ) ) {
			return $variations[ $content_field ];
		}

		return $this->get_original_content( $entity_id, $entity_type, $content_field );
	}

	/**
	 * Set persona-specific content for an entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $entity_type     The entity type (default: 'post').
	 * @param     string $content_field   The content field name.
	 * @param     mixed  $content         The content to store.
	 * @param     string $persona_id      The persona ID.
	 * @return    bool                    Whether the content was set successfully.
	 */
	public function set_content( $entity_id, $entity_type = 'post', $content_field, $content, $persona_id ) {
		// Don't store content for the default persona.
		if ( 'default' === $persona_id ) {
			return false;
		}

		// Get current variations.
		$variations = $this->get_content_variations( $entity_id, $entity_type, $persona_id );

		// Update the content field.
		$variations[ $content_field ] = $content;

		// Save the updated variations.
		return $this->save_content_variations( $entity_id, $entity_type, $persona_id, $variations );
	}

	/**
	 * Get content variations for a persona.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $entity_type     The entity type.
	 * @param     string $persona_id      The persona ID.
	 * @return    array                   The content variations.
	 */
	public function get_content_variations( $entity_id, $entity_type, $persona_id ) {
		$meta_key = $this->get_meta_key( $entity_type, $persona_id );
		$variations = get_post_meta( $entity_id, $meta_key, true );

		return is_array( $variations ) ? $variations : array();
	}

	/**
	 * Save content variations for a persona.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $entity_type     The entity type.
	 * @param     string $persona_id      The persona ID.
	 * @param     array  $variations      The content variations.
	 * @return    bool                    Whether the variations were saved successfully.
	 */
	public function save_content_variations( $entity_id, $entity_type, $persona_id, $variations ) {
		$meta_key = $this->get_meta_key( $entity_type, $persona_id );
		return update_post_meta( $entity_id, $meta_key, $variations );
	}

	/**
	 * Get the original content for an entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $entity_type     The entity type.
	 * @param     string $content_field   The content field name.
	 * @return    mixed                   The original content.
	 */
	public function get_original_content( $entity_id, $entity_type, $content_field ) {
		// For standard post fields.
		if ( 'post' === $entity_type ) {
			$post = get_post( $entity_id );

			if ( ! $post ) {
				return '';
			}

			switch ( $content_field ) {
				case 'content':
					return $post->post_content;
				case 'title':
					return $post->post_title;
				case 'excerpt':
					return $post->post_excerpt;
				default:
					// Try to get from post meta.
					return get_post_meta( $entity_id, $content_field, true );
			}
		}

		// For other entity types, use a filter.
		return apply_filters( 'cme_persona_original_content', '', $entity_id, $entity_type, $content_field );
	}

	/**
	 * Filter post content to show persona-specific content.
	 *
	 * @since     1.1.0
	 * @param     string $content    The content.
	 * @return    string             The filtered content.
	 */
	public function filter_content( $content ) {
		// Skip admin or if no post.
		if ( is_admin() || ! in_the_loop() || ! is_singular() ) {
			return $content;
		}

		$post_id = get_the_ID();
		return $this->get_content( $post_id, 'post', 'content' );
	}

	/**
	 * Filter post title to show persona-specific title.
	 *
	 * @since     1.1.0
	 * @param     string $title      The title.
	 * @param     int    $post_id    The post ID.
	 * @return    string             The filtered title.
	 */
	public function filter_title( $title, $post_id = 0 ) {
		// Skip admin or if no post ID.
		if ( is_admin() || empty( $post_id ) || ! in_the_loop() ) {
			return $title;
		}

		return $this->get_content( $post_id, 'post', 'title' );
	}

	/**
	 * Filter post excerpt to show persona-specific excerpt.
	 *
	 * @since     1.1.0
	 * @param     string $excerpt    The excerpt.
	 * @param     int    $post_id    The post ID or post object.
	 * @return    string             The filtered excerpt.
	 */
	public function filter_excerpt( $excerpt, $post = null ) {
		// Skip admin or if no post.
		if ( is_admin() || empty( $post ) ) {
			return $excerpt;
		}

		$post_id = is_object( $post ) ? $post->ID : $post;
		return $this->get_content( $post_id, 'post', 'excerpt' );
	}

	/**
	 * Get the meta key for storing persona content variations.
	 *
	 * @since     1.1.0
	 * @param     string $entity_type    The entity type.
	 * @param     string $persona_id     The persona ID.
	 * @return    string                 The meta key.
	 */
	private function get_meta_key( $entity_type, $persona_id ) {
		return $this->meta_key_prefix . $entity_type . '_' . $persona_id;
	}

	/**
	 * Delete all persona content for an entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID.
	 * @param     string $entity_type     The entity type.
	 * @return    bool                    Whether the content was deleted successfully.
	 */
	public function delete_all_content( $entity_id, $entity_type = 'post' ) {
		global $wpdb;

		$meta_key_pattern = $this->meta_key_prefix . $entity_type . '_%';

		// Use $wpdb to delete all matching meta keys.
		$result = $wpdb->delete(
			$wpdb->postmeta,
			array(
				'post_id'    => $entity_id,
				'meta_key'   => $meta_key_pattern,
			),
			array(
				'%d',
				'%s',
			)
		);

		if ( false === $result ) {
			return false;
		}

		return true;
	}

	/**
	 * Get all personas that have content for a specific entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID.
	 * @param     string $entity_type     The entity type.
	 * @return    array                   Array of persona IDs.
	 */
	public function get_personas_with_content( $entity_id, $entity_type = 'post' ) {
		global $wpdb;

		$meta_key_pattern = $this->meta_key_prefix . $entity_type . '_%';

		// Get all meta keys matching the pattern.
		$meta_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_key FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s",
				$entity_id,
				$meta_key_pattern
			)
		);

		// Extract the persona IDs from the meta keys.
		$personas = array();
		$prefix_length = strlen( $this->meta_key_prefix . $entity_type . '_' );

		foreach ( $meta_keys as $key ) {
			$personas[] = substr( $key, $prefix_length );
		}

		return $personas;
	}

	/**
	 * Process block content for persona-specific variations.
	 *
	 * @since     1.1.0
	 * @param     string $content         The content with blocks.
	 * @param     string $persona_id      The persona ID.
	 * @return    string                  The processed content.
	 */
	public function process_block_content( $content, $persona_id = null ) {
		// Get the current persona if none specified.
		if ( null === $persona_id ) {
			$persona_manager = Persona_Manager::get_instance();
			$persona_id = $persona_manager->get_current_persona();
		}

		// If it's the default persona, return the original content.
		if ( 'default' === $persona_id ) {
			return $content;
		}

		// Parse blocks.
		$blocks = parse_blocks( $content );
		if ( empty( $blocks ) ) {
			return $content;
		}

		// Process persona blocks.
		$blocks = $this->process_blocks_recursive( $blocks, $persona_id );

		// Render blocks.
		$processed_content = '';
		foreach ( $blocks as $block ) {
			$processed_content .= render_block( $block );
		}

		return $processed_content;
	}

	/**
	 * Process blocks recursively for persona-specific variations.
	 *
	 * @since     1.1.0
	 * @param     array  $blocks          The blocks to process.
	 * @param     string $persona_id      The persona ID.
	 * @return    array                   The processed blocks.
	 */
	private function process_blocks_recursive( $blocks, $persona_id ) {
		foreach ( $blocks as $key => $block ) {
			// Check if this is a persona block.
			if ( 'cme/persona-content' === $block['blockName'] ) {
				// If this block is for a specific persona and it's not the current persona, remove it.
				if ( ! empty( $block['attrs']['persona'] ) && $block['attrs']['persona'] !== $persona_id ) {
					unset( $blocks[ $key ] );
					continue;
				}
			}

			// Process inner blocks recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $key ]['innerBlocks'] = $this->process_blocks_recursive( $block['innerBlocks'], $persona_id );
			}
		}

		// Reindex the array to avoid gaps.
		return array_values( $blocks );
	}
}
