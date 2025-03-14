<?php
/**
 * Personas Post Types registration.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Post Types class.
 *
 * This class handles registration of custom post types for personas.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Personas_Post_Types {
	/**
	 * Post type name for Persona.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private readonly string $persona_post_type;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->persona_post_type = 'persona';
	}

	/**
	 * Register custom post types.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register(): void {
		// Hook into the init action to register custom post types.
		add_action( 'init', array( $this, 'register_post_types' ) );

		// Add meta boxes for gender-specific images.
		add_action( 'add_meta_boxes', array( $this, 'add_persona_meta_boxes' ) );

		// Save meta box data.
		add_action( 'save_post', array( $this, 'save_persona_meta' ) );
	}

	/**
	 * Register the custom post types.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register_post_types(): void {
		// Register Customer Persona post type.
		register_post_type(
			$this->persona_post_type,
			array(
				'labels'            => array(
					'name'                  => _x( 'Customer Personas', 'Post type general name', 'cme-personas' ),
					'singular_name'         => _x( 'Customer Persona', 'Post type singular name', 'cme-personas' ),
					'menu_name'             => _x( 'Personas', 'Admin Menu text', 'cme-personas' ),
					'name_admin_bar'        => _x( 'Customer Persona', 'Add New on Toolbar', 'cme-personas' ),
					'add_new'               => __( 'Add New', 'cme-personas' ),
					'add_new_item'          => __( 'Add New Customer Persona', 'cme-personas' ),
					'new_item'              => __( 'New Customer Persona', 'cme-personas' ),
					'edit_item'             => __( 'Edit Customer Persona', 'cme-personas' ),
					'view_item'             => __( 'View Customer Persona', 'cme-personas' ),
					'all_items'             => __( 'All Customer Personas', 'cme-personas' ),
					'search_items'          => __( 'Search Customer Personas', 'cme-personas' ),
					'parent_item_colon'     => __( 'Parent Customer Personas:', 'cme-personas' ),
					'not_found'             => __( 'No customer personas found.', 'cme-personas' ),
					'not_found_in_trash'    => __( 'No customer personas found in Trash.', 'cme-personas' ),
					'featured_image'        => _x( 'Customer Persona Image', 'Overrides the "Featured Image" phrase', 'cme-personas' ),
					'set_featured_image'    => _x( 'Set persona image', 'Overrides the "Set featured image" phrase', 'cme-personas' ),
					'remove_featured_image' => _x( 'Remove persona image', 'Overrides the "Remove featured image" phrase', 'cme-personas' ),
					'use_featured_image'    => _x( 'Use as persona image', 'Overrides the "Use as featured image" phrase', 'cme-personas' ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => false, // Hide from admin menu, we'll add it via Dashboard.
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

	/**
	 * Add meta boxes for persona gender-specific images.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_persona_meta_boxes(): void {
		add_meta_box(
			'persona_gender_images',
			__( 'Gender-Specific Images', 'cme-personas' ),
			array( $this, 'render_persona_gender_images_metabox' ),
			$this->persona_post_type,
			'normal',
			'high'
		);

		add_meta_box(
			'persona_attributes',
			__( 'Key Attributes', 'cme-personas' ),
			array( $this, 'render_persona_attributes_metabox' ),
			$this->persona_post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Render meta box for persona key attributes.
	 *
	 * @since    1.6.0
	 * @param    \WP_Post $post  The post object.
	 * @return   void
	 */
	public function render_persona_attributes_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'persona_attributes_nonce', 'persona_attributes_nonce' );

		$attributes = get_post_meta( $post->ID, 'persona_attributes', true );

		?>
		<p><?php esc_html_e( 'Enter key attributes for this persona. This information will be displayed on the dashboard and can be used for future AI integration.', 'cme-personas' ); ?></p>
		<p><?php esc_html_e( 'Markdown formatting is supported.', 'cme-personas' ); ?></p>

		<?php
		wp_editor(
			$attributes,
			'persona_attributes',
			array(
				'media_buttons' => false,
				'textarea_rows' => 10,
				'teeny'         => true,
				'quicktags'     => array( 'buttons' => 'strong,em,ul,ol,li,link' ),
			)
		);
	}

	/**
	 * Render meta box for persona gender-specific images.
	 *
	 * @since    1.0.0
	 * @param    \WP_Post $post  The post object.
	 * @return   void
	 */
	public function render_persona_gender_images_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'persona_gender_images_nonce', 'persona_gender_images_nonce' );

		$male_image_id          = get_post_meta( $post->ID, 'persona_image_male', true );
		$female_image_id        = get_post_meta( $post->ID, 'persona_image_female', true );
		$indeterminate_image_id = get_post_meta( $post->ID, 'persona_image_indeterminate', true );

		?>
		<style>
			.persona-gender-image {
				margin-bottom: 15px;
				border-bottom: 1px solid #ddd;
				padding-bottom: 15px;
			}
			.persona-gender-image:last-child {
				border-bottom: none;
				margin-bottom: 0;
			}
			.persona-image-preview {
				margin-top: 10px;
				max-width: 300px;
				max-height: 200px;
				overflow: hidden;
			}
			.persona-image-preview img {
				max-width: 100%;
				height: auto;
			}
		</style>

		<p><?php esc_html_e( 'Select gender-specific images for this persona.', 'cme-personas' ); ?></p>

		<div class="persona-gender-image">
			<label><strong><?php esc_html_e( 'Male Image', 'cme-personas' ); ?></strong></label><br>
			<input type="hidden" name="persona_image_male" id="persona_image_male" value="<?php echo esc_attr( $male_image_id ); ?>">
			<button type="button" class="button persona-upload-image" data-target="persona_image_male">
				<?php esc_html_e( 'Select Image', 'cme-personas' ); ?>
			</button>
			<button type="button" class="button persona-remove-image" data-target="persona_image_male" <?php echo empty( $male_image_id ) ? 'style="display:none;"' : ''; ?>>
				<?php esc_html_e( 'Remove Image', 'cme-personas' ); ?>
			</button>
			<div class="persona-image-preview" id="persona_image_male_preview">
				<?php if ( $male_image_id ) : ?>
					<?php echo wp_get_attachment_image( $male_image_id, 'medium' ); ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="persona-gender-image">
			<label><strong><?php esc_html_e( 'Female Image', 'cme-personas' ); ?></strong></label><br>
			<input type="hidden" name="persona_image_female" id="persona_image_female" value="<?php echo esc_attr( $female_image_id ); ?>">
			<button type="button" class="button persona-upload-image" data-target="persona_image_female">
				<?php esc_html_e( 'Select Image', 'cme-personas' ); ?>
			</button>
			<button type="button" class="button persona-remove-image" data-target="persona_image_female" <?php echo empty( $female_image_id ) ? 'style="display:none;"' : ''; ?>>
				<?php esc_html_e( 'Remove Image', 'cme-personas' ); ?>
			</button>
			<div class="persona-image-preview" id="persona_image_female_preview">
				<?php if ( $female_image_id ) : ?>
					<?php echo wp_get_attachment_image( $female_image_id, 'medium' ); ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="persona-gender-image">
			<label><strong><?php esc_html_e( 'Indeterminate Gender Image', 'cme-personas' ); ?></strong></label><br>
			<input type="hidden" name="persona_image_indeterminate" id="persona_image_indeterminate" value="<?php echo esc_attr( $indeterminate_image_id ); ?>">
			<button type="button" class="button persona-upload-image" data-target="persona_image_indeterminate">
				<?php esc_html_e( 'Select Image', 'cme-personas' ); ?>
			</button>
			<button type="button" class="button persona-remove-image" data-target="persona_image_indeterminate" <?php echo empty( $indeterminate_image_id ) ? 'style="display:none;"' : ''; ?>>
				<?php esc_html_e( 'Remove Image', 'cme-personas' ); ?>
			</button>
			<div class="persona-image-preview" id="persona_image_indeterminate_preview">
				<?php if ( $indeterminate_image_id ) : ?>
					<?php echo wp_get_attachment_image( $indeterminate_image_id, 'medium' ); ?>
				<?php endif; ?>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Image upload functionality.
			$('.persona-upload-image').click(function(e) {
				e.preventDefault();

				const button = $(this);
				const targetField = button.data('target');

				// Create media frame.
				const mediaFrame = wp.media({
					title: '<?php esc_html_e( 'Select or Upload Image', 'cme-personas' ); ?>',
					button: {
						text: '<?php esc_html_e( 'Use this image', 'cme-personas' ); ?>'
					},
					multiple: false
				});

				// When image selected, run callback.
				mediaFrame.on('select', function() {
					const attachment = mediaFrame.state().get('selection').first().toJSON();
					$('#' + targetField).val(attachment.id);
					$('#' + targetField + '_preview').html('<img src="' + attachment.url + '">');
					button.next('.persona-remove-image').show();
				});

				// Open media frame.
				mediaFrame.open();
			});

			// Image removal functionality.
			$('.persona-remove-image').click(function(e) {
				e.preventDefault();

				const button = $(this);
				const targetField = button.data('target');

				$('#' + targetField).val('');
				$('#' + targetField + '_preview').empty();
				button.hide();
			});
		});
		</script>
		<?php
	}

	/**
	 * Save persona meta box data.
	 *
	 * @since    1.0.0
	 * @param    int $post_id  The post ID.
	 * @return   void
	 */
	public function save_persona_meta( int $post_id ): void {
		// Check if our nonce is set.
		if ( ! isset( $_POST['persona_gender_images_nonce'] ) ) {
			return;
		}

		// Verify the nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['persona_gender_images_nonce'] ) ), 'persona_gender_images_nonce' ) ) {
			return;
		}

		// If this is an autosave, don't do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get the persona title for tagging.
		$persona_title = get_the_title( $post_id );
		$words         = explode( ' ', $persona_title );
		$persona_slug  = '';

		// Use first two words for the tag.
		if ( count( $words ) >= 2 ) {
			$persona_slug = sanitize_title( $words[0] . ' ' . $words[1] );
		} else {
			$persona_slug = sanitize_title( $persona_title );
		}

		// Save male image.
		if ( isset( $_POST['persona_image_male'] ) ) {
			$male_image_id = sanitize_text_field( wp_unslash( $_POST['persona_image_male'] ) );
			update_post_meta( $post_id, 'persona_image_male', $male_image_id );
			// Media taxonomy functionality has been removed.
		}

		// Save female image.
		if ( isset( $_POST['persona_image_female'] ) ) {
			$female_image_id = sanitize_text_field( wp_unslash( $_POST['persona_image_female'] ) );
			update_post_meta( $post_id, 'persona_image_female', $female_image_id );
			// Media taxonomy functionality has been removed.
		}

		// Save indeterminate image.
		if ( isset( $_POST['persona_image_indeterminate'] ) ) {
			$indeterminate_image_id = sanitize_text_field( wp_unslash( $_POST['persona_image_indeterminate'] ) );
			update_post_meta( $post_id, 'persona_image_indeterminate', $indeterminate_image_id );
			// Media taxonomy functionality has been removed.
		}

		// Save persona attributes.
		if ( isset( $_POST['persona_attributes_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['persona_attributes_nonce'] ) ), 'persona_attributes_nonce' ) ) {
			if ( isset( $_POST['persona_attributes'] ) ) {
				$attributes = wp_kses_post( wp_unslash( $_POST['persona_attributes'] ) );
				update_post_meta( $post_id, 'persona_attributes', $attributes );
			}
		}
	}
}
