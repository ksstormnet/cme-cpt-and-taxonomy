<?php
/**
 * Custom Post Types registration.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * Custom Post Types class.
 *
 * This class handles registration of custom post types.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */
class Custom_Post_Types {
	/**
	 * Post type name for Persona.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private readonly string $persona_post_type = 'persona';

	/**
	 * Register custom post types.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register(): void {
		// Hook into the init action to register custom post types.
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register the custom post types.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register_post_types(): void {
		// Register Customer Persona post type
		register_post_type(
			$this->persona_post_type,
			array(
				'labels'            => array(
					'name'                  => _x( 'Customer Personas', 'Post type general name', 'cme-cpt-and-taxonomy' ),
					'singular_name'         => _x( 'Customer Persona', 'Post type singular name', 'cme-cpt-and-taxonomy' ),
					'menu_name'             => _x( 'Customer Personas', 'Admin Menu text', 'cme-cpt-and-taxonomy' ),
					'name_admin_bar'        => _x( 'Customer Persona', 'Add New on Toolbar', 'cme-cpt-and-taxonomy' ),
					'add_new'               => __( 'Add New', 'cme-cpt-and-taxonomy' ),
					'add_new_item'          => __( 'Add New Customer Persona', 'cme-cpt-and-taxonomy' ),
					'new_item'              => __( 'New Customer Persona', 'cme-cpt-and-taxonomy' ),
					'edit_item'             => __( 'Edit Customer Persona', 'cme-cpt-and-taxonomy' ),
					'view_item'             => __( 'View Customer Persona', 'cme-cpt-and-taxonomy' ),
					'all_items'             => __( 'All Customer Personas', 'cme-cpt-and-taxonomy' ),
					'search_items'          => __( 'Search Customer Personas', 'cme-cpt-and-taxonomy' ),
					'parent_item_colon'     => __( 'Parent Customer Personas:', 'cme-cpt-and-taxonomy' ),
					'not_found'             => __( 'No customer personas found.', 'cme-cpt-and-taxonomy' ),
					'not_found_in_trash'    => __( 'No customer personas found in Trash.', 'cme-cpt-and-taxonomy' ),
					'featured_image'        => _x( 'Customer Persona Image', 'Overrides the "Featured Image" phrase', 'cme-cpt-and-taxonomy' ),
					'set_featured_image'    => _x( 'Set persona image', 'Overrides the "Set featured image" phrase', 'cme-cpt-and-taxonomy' ),
					'remove_featured_image' => _x( 'Remove persona image', 'Overrides the "Remove featured image" phrase', 'cme-cpt-and-taxonomy' ),
					'use_featured_image'    => _x( 'Use as persona image', 'Overrides the "Use as featured image" phrase', 'cme-cpt-and-taxonomy' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_in_nav_menus' => true,
				'show_in_rest'      => true,
				'menu_icon'         => 'dashicons-groups',
				'supports'          => array( 'title', 'excerpt', 'thumbnail' ),
				'has_archive'       => false,
				'rewrite'           => array( 'slug' => 'persona' ),
				'query_var'         => true,
				'menu_position'     => 5,
				'capability_type'   => 'post',
				'hierarchical'      => false,
			)
		);
	}
}
