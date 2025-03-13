<?php
/**
 * Persona Integrator Class
 *
 * Handles integration of persona functionality into the WordPress system.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Persona Integrator Class
 *
 * This class integrates all persona functionality into the WordPress system.
 * It initializes the core classes and hooks them into the WordPress lifecycle.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Persona_Integrator {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Integrator    $instance    Singleton instance of the class.
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
	 * Instance of the Persona_Content class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Persona_Content    $persona_content    Instance of the Persona_Content class.
	 */
	private $persona_content;

	/**
	 * Instance of the Personas_API class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Personas_API    $personas_api    Instance of the Personas_API class.
	 */
	private $personas_api;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Persona_Integrator    The singleton instance.
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
		// Include required files.
		$this->includes();

		// Initialize instances.
		$this->persona_manager = Persona_Manager::get_instance();
		$this->persona_content = Persona_Content::get_instance();
		$this->personas_api    = Personas_API::get_instance();

		// Set up hooks.
		$this->setup_hooks();

		// Set up global functions.
		$this->setup_global_functions();
	}

	/**
	 * Include required files.
	 *
	 * @since    1.1.0
	 */
	private function includes() {
		// Core classes - only include if they're not already included by the autoloader.
		$base_path = plugin_dir_path( __DIR__ ) . 'includes/';

		if ( ! class_exists( '\\CME_Personas\\Persona_Manager' ) ) {
			require_once $base_path . 'class-persona-manager.php';
		}

		if ( ! class_exists( '\\CME_Personas\\Persona_Content' ) ) {
			require_once $base_path . 'class-persona-content.php';
		}

		if ( ! class_exists( '\\CME_Personas\\Personas_API' ) ) {
			require_once $base_path . 'class-personas-api.php';
		}
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.1.0
	 */
	private function setup_hooks() {
		// Hook into activation.
		register_activation_hook( \CME_PERSONAS_FILE, array( $this, 'activate' ) );

		// Hook into deactivation.
		register_deactivation_hook( \CME_PERSONAS_FILE, array( $this, 'deactivate' ) );

		// Hook into plugin uninstall.
		register_uninstall_hook( \CME_PERSONAS_FILE, array( __CLASS__, 'uninstall' ) );

		// Hook into admin.
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Register scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		// Set up admin meta boxes for editing persona-specific content.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_persona_content' ) );
	}

	/**
	 * Set up global functions.
	 *
	 * @since    1.1.0
	 */
	private function setup_global_functions() {
		if ( ! function_exists( 'cme_get_current_persona' ) ) {
			/**
			 * Get the current persona.
			 *
			 * @since     1.1.0
			 * @return    string    The current persona identifier.
			 */
			function cme_get_current_persona() {
				$api = Personas_API::get_instance();
				return $api->get_current_persona();
			}
		}

		if ( ! function_exists( 'cme_set_persona' ) ) {
			/**
			 * Set the active persona.
			 *
			 * @since     1.1.0
			 * @param     string $persona_id    The persona identifier to set.
			 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
			 * @return    bool                  Whether the persona was set successfully.
			 */
			function cme_set_persona( $persona_id, $set_cookie = true ) {
				$api = Personas_API::get_instance();
				return $api->set_persona( $persona_id, $set_cookie );
			}
		}

		if ( ! function_exists( 'cme_get_persona_content' ) ) {
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
			function cme_get_persona_content( $entity_id, $entity_type = 'post', $content_field = 'content', $persona_id = null ) {
				$api = Personas_API::get_instance();
				return $api->get_content( $entity_id, $entity_type, $content_field, $persona_id );
			}
		}

		if ( ! function_exists( 'cme_get_all_personas' ) ) {
			/**
			 * Get all available personas.
			 *
			 * @since     1.1.0
			 * @return    array    Array of available personas in format [id => name].
			 */
			function cme_get_all_personas() {
				$api = Personas_API::get_instance();
				return $api->get_all_personas();
			}
		}
	}

	/**
	 * Register assets.
	 *
	 * @since    1.1.0
	 */
	public function register_assets() {
		// Frontend CSS.
		wp_register_style(
			'cme-personas',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/css/personas.css',
			array(),
			\CME_PERSONAS_VERSION,
			'all'
		);

		// Frontend JS.
		wp_register_script(
			'cme-personas',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/js/personas.js',
			array( 'jquery' ),
			\CME_PERSONAS_VERSION,
			true
		);

		// Enqueue frontend assets.
		wp_enqueue_style( 'cme-personas' );
		wp_enqueue_script( 'cme-personas' );

		// Pass data to script.
		wp_localize_script(
			'cme-personas',
			'cmePersonas',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'cme_personas_nonce' ),
				'currentPersona' => $this->persona_manager->get_current_persona(),
			)
		);
	}

	/**
	 * Activation hook.
	 *
	 * @since    1.1.0
	 */
	public function activate() {
		// Nothing to do yet.
	}

	/**
	 * Deactivation hook.
	 *
	 * @since    1.1.0
	 */
	public function deactivate() {
		// Nothing to do yet.
	}

	/**
	 * Uninstall hook.
	 *
	 * @since    1.1.0
	 */
	public static function uninstall() {
		// Nothing to do yet.
	}

	/**
	 * Admin initialization.
	 *
	 * @since    1.1.0
	 */
	public function admin_init() {
		// Add admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	/**
	 * Register admin assets.
	 *
	 * @since    1.1.0
	 */
	public function admin_assets() {
		// Admin CSS.
		wp_register_style(
			'cme-personas-admin',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'admin/css/personas-admin.css',
			array(),
			\CME_PERSONAS_VERSION,
			'all'
		);

		// Admin JS.
		wp_register_script(
			'cme-personas-admin',
			plugin_dir_url( \CME_PERSONAS_FILE ) . 'admin/js/personas-admin.js',
			array( 'jquery' ),
			\CME_PERSONAS_VERSION,
			true
		);

		// Enqueue admin assets.
		wp_enqueue_style( 'cme-personas-admin' );
		wp_enqueue_script( 'cme-personas-admin' );

		// Pass data to script.
		wp_localize_script(
			'cme-personas-admin',
			'cmePersonasAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cme_personas_admin_nonce' ),
				'personas' => $this->persona_manager->get_all_personas(),
			)
		);
	}

	/**
	 * Register meta boxes.
	 *
	 * @since    1.1.0
	 */
	public function register_meta_boxes() {
		// Get post types that support persona content.
		$post_types = apply_filters( 'cme_persona_content_post_types', array( 'post', 'page' ) );

		// Add meta box to each post type.
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'cme_persona_content',
				__( 'Persona Content', 'cme-personas' ),
				array( $this, 'render_persona_content_meta_box' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render persona content meta box.
	 *
	 * @since    1.1.0
	 * @param    \WP_Post $post    The post object.
	 */
	public function render_persona_content_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'cme_persona_content_meta_box', 'cme_persona_content_nonce' );

		// Get all personas.
		$personas = $this->persona_manager->get_all_personas();

		// Remove the default persona since we don't store content for it.
		unset( $personas['default'] );

		// Check if we have any personas.
		if ( empty( $personas ) ) {
			echo '<p>' . esc_html__( 'No personas found. Create personas first to add persona-specific content.', 'cme-personas' ) . '</p>';
			return;
		}

		// Get personas that have content for this post.
		$personas_with_content = $this->persona_content->get_personas_with_content( $post->ID );

		// Start output.
		echo '<div class="cme-persona-content-tabs">';

		// Tabs.
		echo '<div class="cme-persona-tabs-nav">';
		foreach ( $personas as $id => $name ) {
			$active = in_array( $id, $personas_with_content, true ) ? 'has-content' : '';
			echo '<button type="button" class="cme-persona-tab ' . esc_attr( $active ) . '" data-persona="' . esc_attr( $id ) . '">' . esc_html( $name ) . '</button>';
		}
		echo '</div>';

		// Content panels.
		echo '<div class="cme-persona-tabs-content">';
		foreach ( $personas as $id => $name ) {
			// Get content variations for this persona.
			$variations = $this->persona_content->get_content_variations( $post->ID, 'post', $id );

			// Output panel.
			echo '<div class="cme-persona-tab-panel" data-persona="' . esc_attr( $id ) . '">';
			/* translators: %s: Persona name */
			$content_heading = sprintf( __( 'Content for %s', 'cme-personas' ), $name );
			echo '<h3>' . esc_html( $content_heading ) . '</h3>';

			// Title.
			echo '<div class="cme-persona-field">';
			echo '<label for="cme_persona_' . esc_attr( $id ) . '_title">' . esc_html__( 'Title', 'cme-personas' ) . '</label>';
			echo '<input type="text" id="cme_persona_' . esc_attr( $id ) . '_title" name="cme_persona_content[' . esc_attr( $id ) . '][title]" value="' . esc_attr( $variations['title'] ?? '' ) . '" class="widefat">';
			echo '</div>';

			// Content.
			echo '<div class="cme-persona-field">';
			echo '<label for="cme_persona_' . esc_attr( $id ) . '_content">' . esc_html__( 'Content', 'cme-personas' ) . '</label>';
			$content = $variations['content'] ?? '';
			wp_editor(
				$content,
				'cme_persona_' . esc_attr( $id ) . '_content',
				array(
					'textarea_name' => 'cme_persona_content[' . esc_attr( $id ) . '][content]',
					'media_buttons' => true,
					'textarea_rows' => 10,
				)
			);
			echo '</div>';

			// Excerpt.
			echo '<div class="cme-persona-field">';
			echo '<label for="cme_persona_' . esc_attr( $id ) . '_excerpt">' . esc_html__( 'Excerpt', 'cme-personas' ) . '</label>';
			echo '<textarea id="cme_persona_' . esc_attr( $id ) . '_excerpt" name="cme_persona_content[' . esc_attr( $id ) . '][excerpt]" rows="3" class="widefat">' . esc_textarea( $variations['excerpt'] ?? '' ) . '</textarea>';
			echo '</div>';

			// Delete button.
			echo '<div class="cme-persona-actions">';
			echo '<button type="button" class="button cme-persona-delete" data-persona="' . esc_attr( $id ) . '">' . esc_html__( 'Delete Content for this Persona', 'cme-personas' ) . '</button>';
			echo '</div>';

			echo '</div>'; // Close panel.
		}
		echo '</div>'; // Close content panels.

		echo '</div>'; // Close tabs container.

		// Add some JavaScript to handle tab switching.
		?>
		<script>
			jQuery(document).ready(function($) {
				// Tab switching.
				$('.cme-persona-tab').on('click', function() {
					var persona = $(this).data('persona');
					$('.cme-persona-tab-panel').hide();
					$('.cme-persona-tab-panel[data-persona="' + persona + '"]').show();
					$('.cme-persona-tab').removeClass('active');
					$(this).addClass('active');
				});

				// Show the first tab by default.
				$('.cme-persona-tab:first').click();

				// Delete button.
				$('.cme-persona-delete').on('click', function() {
					/* eslint-disable no-alert */
					// translators: Confirmation message when deleting persona content
					if (confirm('<?php echo esc_js( __( 'Are you sure you want to delete the content for this persona? This cannot be undone.', 'cme-personas' ) ); ?>')) {
						var persona = $(this).data('persona');
						$('#cme_persona_' + persona + '_title').val('');
						// For the content editor, we need to use the tinymce API.
						if (typeof tinymce !== 'undefined' && tinymce.get('cme_persona_' + persona + '_content')) {
							tinymce.get('cme_persona_' + persona + '_content').setContent('');
						} else {
							$('#cme_persona_' + persona + '_content').val('');
						}
						$('#cme_persona_' + persona + '_excerpt').val('');
					}
					/* eslint-enable no-alert */
				});
			});
		</script>
		<?php
	}

	/**
	 * Save persona content.
	 *
	 * @since    1.1.0
	 * @param    int $post_id    The post ID.
	 */
	public function save_persona_content( $post_id ) {
		// Check if nonce is set.
		if ( ! isset( $_POST['cme_persona_content_nonce'] ) ) {
			return;
		}

		// Verify nonce.
		$nonce = sanitize_text_field( wp_unslash( $_POST['cme_persona_content_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'cme_persona_content_meta_box' ) ) {
			return;
		}

		// If this is an autosave, don't do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get all personas.
		$personas = $this->persona_manager->get_all_personas();

		// Remove the default persona since we don't store content for it.
		unset( $personas['default'] );

		// Get submitted content.
		$submitted_content = array();
		if ( isset( $_POST['cme_persona_content'] ) ) {
			$submitted_content = map_deep( wp_unslash( $_POST['cme_persona_content'] ), 'sanitize_text_field' );
		}

		// Process each persona.
		foreach ( $personas as $id => $name ) {
			if ( isset( $submitted_content[ $id ] ) ) {
				// Sanitize content.
				$content = array(
					'title'   => isset( $submitted_content[ $id ]['title'] ) ? sanitize_text_field( $submitted_content[ $id ]['title'] ) : '',
					'content' => isset( $submitted_content[ $id ]['content'] ) ? wp_kses_post( $submitted_content[ $id ]['content'] ) : '',
					'excerpt' => isset( $submitted_content[ $id ]['excerpt'] ) ? sanitize_textarea_field( $submitted_content[ $id ]['excerpt'] ) : '',
				);

				// Only save if not empty.
				if ( ! empty( $content['title'] ) || ! empty( $content['content'] ) || ! empty( $content['excerpt'] ) ) {
					$this->persona_content->save_content_variations( $post_id, 'post', $id, $content );
				}
			}
		}
	}
}
