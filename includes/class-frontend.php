<?php
/**
 * Frontend Integration Class
 *
 * Handles frontend functionality for Persona content.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Frontend Integration Class
 *
 * This class provides shortcodes, template functions, and frontend persona
 * switching functionality for the Personas plugin.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */
class Frontend {

	/**
	 * Instance of the class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Frontend    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance of the Persona_Manager class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Persona_Manager    $persona_manager    Instance of the Persona_Manager class.
	 */
	private $persona_manager;

	/**
	 * Instance of the Persona_Content class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Persona_Content    $persona_content    Instance of the Persona_Content class.
	 */
	private $persona_content;

	/**
	 * Instance of the Personas_API class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Personas_API    $personas_api    Instance of the Personas_API class.
	 */
	private $personas_api;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.3.0
	 * @return    Frontend    The singleton instance.
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
	 * @since    1.3.0
	 */
	private function __construct() {
		// Initialize instances.
		$this->persona_manager = Persona_Manager::get_instance();
		$this->persona_content = Persona_Content::get_instance();
		$this->personas_api    = Personas_API::get_instance();

		// Setup hooks.
		$this->setup_hooks();
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.3.0
	 */
	private function setup_hooks() {
		// Register shortcodes.
		add_shortcode( 'persona_content', array( $this, 'persona_content_shortcode' ) );
		add_shortcode( 'persona_switcher', array( $this, 'persona_switcher_shortcode' ) );
		add_shortcode( 'if_persona', array( $this, 'if_persona_shortcode' ) );

		// Add AJAX handlers for frontend persona switching.
		add_action( 'wp_ajax_cme_switch_persona', array( $this, 'ajax_switch_persona' ) );
		add_action( 'wp_ajax_nopriv_cme_switch_persona', array( $this, 'ajax_switch_persona' ) );

		// Add frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since    1.3.0
	 */
	public function enqueue_frontend_assets() {
		// Only load assets if they haven't been loaded yet.
		if ( ! wp_script_is( 'cme-personas', 'enqueued' ) ) {
			// Frontend CSS.
			wp_enqueue_style(
				'cme-personas-frontend',
				plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/css/personas-frontend.css',
				array(),
				\CME_PERSONAS_VERSION,
				'all'
			);

			// Frontend JS.
			wp_enqueue_script(
				'cme-personas-frontend',
				plugin_dir_url( \CME_PERSONAS_FILE ) . 'public/js/personas-frontend.js',
				array( 'jquery' ),
				\CME_PERSONAS_VERSION,
				true
			);

			// Pass data to script.
			wp_localize_script(
				'cme-personas-frontend',
				'cmePersonas',
				array(
					'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'cme_personas_nonce' ),
					'currentPersona' => $this->personas_api->get_current_persona(),
				)
			);
		}
	}

	/**
	 * Shortcode for persona-specific content.
	 *
	 * Usage: [persona_content persona="business" entity_id="123" entity_type="post" field="content"]
	 *        [persona_content persona="family"]Default content for other personas[/persona_content]
	 *
	 * @since     1.3.0
	 * @param     array  $atts      Shortcode attributes.
	 * @param     string $content   Default content (optional).
	 * @return    string            The persona-specific content or default content.
	 */
	public function persona_content_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'persona'     => null, // If null, will use current persona.
				'entity_id'   => null, // If null, will use current post.
				'entity_type' => 'post',
				'field'       => 'content',
			),
			$atts,
			'persona_content'
		);

		// If no entity_id is provided, use current post.
		if ( null === $atts['entity_id'] ) {
			global $post;
			$atts['entity_id'] = $post ? $post->ID : 0;
		}

		// If entity_id is still null or 0, return the default content.
		if ( empty( $atts['entity_id'] ) ) {
			return do_shortcode( $content );
		}

		// Get persona-specific content.
		$persona_content = $this->personas_api->get_content(
			$atts['entity_id'],
			$atts['entity_type'],
			$atts['field'],
			$atts['persona']
		);

		// If we have persona-specific content, return it.
		if ( $persona_content ) {
			return $persona_content;
		}

		// Otherwise return the default content.
		return do_shortcode( $content );
	}

	/**
	 * Shortcode for conditional persona content.
	 *
	 * Usage: [if_persona is="business"]Business-specific content[/if_persona]
	 *        [if_persona is="family,luxury"]Content for family and luxury personas[/if_persona]
	 *        [if_persona not="business"]Content for non-business personas[/if_persona]
	 *
	 * @since     1.3.0
	 * @param     array  $atts      Shortcode attributes.
	 * @param     string $content   Content to conditionally display.
	 * @return    string            The content if conditions are met, empty string otherwise.
	 */
	public function if_persona_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'is'  => null,
				'not' => null,
			),
			$atts,
			'if_persona'
		);

		// Get current persona.
		$current_persona = $this->personas_api->get_current_persona();

		// If 'is' attribute is set, check if current persona matches.
		if ( null !== $atts['is'] ) {
			$allowed_personas = array_map( 'trim', explode( ',', $atts['is'] ) );
			if ( ! in_array( $current_persona, $allowed_personas, true ) ) {
				return '';
			}
		}

		// If 'not' attribute is set, check if current persona does not match.
		if ( null !== $atts['not'] ) {
			$excluded_personas = array_map( 'trim', explode( ',', $atts['not'] ) );
			if ( in_array( $current_persona, $excluded_personas, true ) ) {
				return '';
			}
		}

		// If we get here, the conditions are met.
		return do_shortcode( $content );
	}

	/**
	 * Shortcode for persona switcher.
	 *
	 * Usage: [persona_switcher]
	 *        [persona_switcher display="dropdown" button_text="Switch Persona"]
	 *
	 * @since     1.3.0
	 * @param     array $atts    Shortcode attributes.
	 * @return    string         HTML output for the persona switcher.
	 */
	public function persona_switcher_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'display'     => 'buttons', // 'buttons' or 'dropdown'.
				'button_text' => __( 'Select Persona', 'cme-personas' ),
				'class'       => '', // Additional CSS classes.
			),
			$atts,
			'persona_switcher'
		);

		// Get all personas.
		$personas = $this->persona_manager->get_all_personas();

		// Get current persona.
		$current_persona = $this->personas_api->get_current_persona();

		// Start building output.
		$output = '<div class="cme-persona-switcher ' . esc_attr( $atts['class'] ) . '" data-display="' . esc_attr( $atts['display'] ) . '">';

		if ( 'dropdown' === $atts['display'] ) {
			// Dropdown display.
			$output .= '<label for="cme-persona-select">' . esc_html( $atts['button_text'] ) . '</label>';
			$output .= '<select id="cme-persona-select" class="cme-persona-select">';

			foreach ( $personas as $id => $name ) {
				$selected = $id === $current_persona ? ' selected' : '';
				$output  .= '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . esc_html( $name ) . '</option>';
			}

			$output .= '</select>';
		} else {
			// Buttons display.
			$output .= '<div class="cme-persona-buttons">';

			foreach ( $personas as $id => $name ) {
				$active  = $id === $current_persona ? ' active' : '';
				$output .= '<button type="button" class="cme-persona-button' . $active . '" data-persona="' . esc_attr( $id ) . '">' . esc_html( $name ) . '</button>';
			}

			$output .= '</div>';
		}

		$output .= '</div>';

		// Ensure frontend assets are loaded.
		$this->enqueue_frontend_assets();

		return $output;
	}

	/**
	 * AJAX handler for switching personas.
	 *
	 * This method validates the request, changes the user's persona preference,
	 * and returns JSON response with success or error messages.
	 *
	 * @since    1.3.0
	 */
	public function ajax_switch_persona() {
		// Check nonce.
		if ( ! check_ajax_referer( 'cme_personas_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'cme-personas' ) ) );
		}

		// Get persona ID.
		$persona_id = isset( $_POST['persona'] ) ? sanitize_key( $_POST['persona'] ) : '';
		if ( empty( $persona_id ) ) {
			wp_send_json_error( array( 'message' => __( 'No persona specified.', 'cme-personas' ) ) );
		}

		// Set persona.
		$success = $this->personas_api->set_persona( $persona_id );

		if ( $success ) {
			wp_send_json_success(
				array(
					'persona' => $persona_id,
					'message' => sprintf(
						/* translators: %s: persona name */
						__( 'Successfully switched to %s persona.', 'cme-personas' ),
						$this->persona_manager->get_persona_name( $persona_id )
					),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to switch persona.', 'cme-personas' ),
				)
			);
		}
	}
}
