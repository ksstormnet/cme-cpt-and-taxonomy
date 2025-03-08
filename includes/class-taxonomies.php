<?php
/**
 * Taxonomies registration.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * Taxonomies class.
 *
 * This class handles registration of taxonomies.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */
class Taxonomies {
	/**
	 * Taxonomy name for media tags.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private readonly string $media_taxonomy;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->media_taxonomy = 'media_tag';
	}

	/**
	 * Register taxonomies.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register(): void {
		// Hook into the init action to register taxonomies.
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register the taxonomies.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register_taxonomies(): void {
		// Register custom taxonomy for the media library.
		register_taxonomy(
			$this->media_taxonomy,
			array( 'attachment', 'persona' ),
			array(
				'labels'             => array(
					'name'                       => _x( 'Media Tags', 'taxonomy general name', 'cme-cpt-and-taxonomy' ),
					'singular_name'              => _x( 'Media Tag', 'taxonomy singular name', 'cme-cpt-and-taxonomy' ),
					'search_items'               => __( 'Search Media Tags', 'cme-cpt-and-taxonomy' ),
					'popular_items'              => __( 'Popular Media Tags', 'cme-cpt-and-taxonomy' ),
					'all_items'                  => __( 'All Media Tags', 'cme-cpt-and-taxonomy' ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Media Tag', 'cme-cpt-and-taxonomy' ),
					'update_item'                => __( 'Update Media Tag', 'cme-cpt-and-taxonomy' ),
					'add_new_item'               => __( 'Add New Media Tag', 'cme-cpt-and-taxonomy' ),
					'new_item_name'              => __( 'New Media Tag Name', 'cme-cpt-and-taxonomy' ),
					'separate_items_with_commas' => __( 'Separate media tags with commas', 'cme-cpt-and-taxonomy' ),
					'add_or_remove_items'        => __( 'Add or remove media tags', 'cme-cpt-and-taxonomy' ),
					'choose_from_most_used'      => __( 'Choose from the most used media tags', 'cme-cpt-and-taxonomy' ),
					'not_found'                  => __( 'No media tags found.', 'cme-cpt-and-taxonomy' ),
					'menu_name'                  => __( 'Media Tags', 'cme-cpt-and-taxonomy' ),
					'back_to_items'              => __( '← Back to Media Tags', 'cme-cpt-and-taxonomy' ),
				),
				'hierarchical'       => false,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'media-tag' ),
				'show_in_menu'       => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => true,
				'show_in_quick_edit' => true,
			)
		);

		// Make taxonomy work with attachments
		add_filter( 'attachment_fields_to_edit', array( $this, 'add_taxonomy_field' ), 10, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, 'save_taxonomy_field' ), 10, 2 );
	}

	/**
	 * Add taxonomy field to attachment edit screen.
	 *
	 * @since    1.0.0
	 * @param    array  $form_fields  Array of form fields.
	 * @param    object $post         WP_Post object.
	 * @return   array
	 */
	public function add_taxonomy_field( array $form_fields, $post ): array {
		$taxonomy = get_taxonomy( $this->media_taxonomy );

		if ( ! $taxonomy ) {
			return $form_fields;
		}

		$terms  = get_the_terms( $post->ID, $this->media_taxonomy );
		$values = array();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$values[] = $term->name;
			}
		}

		$form_fields[ $this->media_taxonomy ] = array(
			'label' => $taxonomy->labels->name,
			'input' => 'text',
			'value' => join( ', ', $values ),
			'helps' => sprintf( __( 'Separate %s with commas.', 'cme-cpt-and-taxonomy' ), strtolower( $taxonomy->labels->name ) ),
		);

		return $form_fields;
	}

	/**
	 * Save taxonomy field from attachment edit screen.
	 *
	 * @since    1.0.0
	 * @param    array $post        WP_Post array.
	 * @param    array $attachment  Attachment fields.
	 * @return   array
	 */
	public function save_taxonomy_field( array $post, array $attachment ): array {
		if ( isset( $attachment[ $this->media_taxonomy ] ) ) {
			$taxonomy = get_taxonomy( $this->media_taxonomy );

			if ( ! $taxonomy ) {
				return $post;
			}

			$terms = array_map( 'trim', explode( ',', $attachment[ $this->media_taxonomy ] ) );

			// Set the terms for the attachment
			wp_set_object_terms( $post['ID'], $terms, $this->media_taxonomy, false );
		}

		return $post;
	}
}
