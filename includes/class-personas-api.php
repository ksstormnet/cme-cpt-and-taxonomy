<?php
/**
 * Personas API Class
 *
 * Provides public API functions for the Personas system.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas API Class
 *
 * This class provides public functions for interacting with the Personas system.
 * It acts as a facade for the Persona_Manager and Persona_Content classes,
 * offering simple functions that can be used in templates and other plugins.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */
class Personas_API {

	/**
	 * Instance of the class.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      Personas_API    $instance    Singleton instance of the class.
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
	 * Get the singleton instance of the class.
	 *
	 * @since     1.1.0
	 * @return    Personas_API    The singleton instance.
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
		$this->persona_manager = Persona_Manager::get_instance();
		$this->persona_content = Persona_Content::get_instance();

		// Initialize shortcodes.
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes.
	 *
	 * @since    1.1.0
	 * @return   void
	 */
	public function register_shortcodes() {
		add_shortcode( 'persona_content', array( $this, 'persona_content_shortcode' ) );
		add_shortcode( 'persona_switcher', array( $this, 'persona_switcher_shortcode' ) );
	}

	/**
	 * Shortcode for persona-specific content.
	 *
	 * Usage: [persona_content persona="persona-id"]Content for this persona[/persona_content]
	 *
	 * @since     1.1.0
	 * @param     array  $atts      Shortcode attributes.
	 * @param     string $content   Shortcode content.
	 * @return    string            Processed content.
	 */
	public function persona_content_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'persona' => '',
				'default' => '',
			),
			$atts,
			'persona_content'
		);

		// If no content, return empty.
		if ( null === $content ) {
			return '';
		}

		// Get current persona.
		$current_persona = $this->get_current_persona();

		// If this content is for all personas or the current persona, show it.
		if ( empty( $atts['persona'] ) || $atts['persona'] === $current_persona ) {
			return do_shortcode( $content );
		}

		// Otherwise, return default content if provided.
		if ( ! empty( $atts['default'] ) ) {
			return $atts['default'];
		}

		// No match, return empty.
		return '';
	}

	/**
	 * Shortcode for persona switcher.
	 *
	 * Usage: [persona_switcher]
	 *
	 * @since     1.1.0
	 * @param     array $atts    Shortcode attributes.
	 * @return    string         The persona switcher HTML.
	 */
	public function persona_switcher_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'class' => 'persona-switcher',
				'label' => __( 'Switch Persona', 'cme-personas' ),
			),
			$atts,
			'persona_switcher'
		);

		// Get all available personas.
		$personas = $this->get_all_personas();
		if ( empty( $personas ) ) {
			return '';
		}

		// Get current persona.
		$current_persona = $this->get_current_persona();

		// Build the switcher HTML.
		$html  = '<div class="' . esc_attr( $atts['class'] ) . '">';
		$html .= '<label>' . esc_html( $atts['label'] ) . '</label>';
		$html .= '<select id="persona-switcher" data-current="' . esc_attr( $current_persona ) . '">';

		foreach ( $personas as $id => $name ) {
			$selected = selected( $id, $current_persona, false );
			$html    .= '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . esc_html( $name ) . '</option>';
		}

		$html .= '</select>';
		$html .= '</div>';

		// Add the JavaScript to handle switching.
		$html .= "<script>
			document.addEventListener('DOMContentLoaded', function() {
				var switcher = document.getElementById('persona-switcher');
				if (switcher) {
					switcher.addEventListener('change', function() {
						var persona = this.value;
						var url = new URL(window.location.href);
						url.searchParams.set('persona', persona);
						window.location.href = url.toString();
					});
				}
			});
		</script>";

		return $html;
	}

	/**
	 * Get the current persona.
	 *
	 * @since     1.1.0
	 * @return    string    The current persona identifier.
	 */
	public function get_current_persona() {
		return $this->persona_manager->get_current_persona();
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
		return $this->persona_manager->set_persona( $persona_id, $set_cookie );
	}

	/**
	 * Check if a persona is valid.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier to check.
	 * @return    bool                  Whether the persona is valid.
	 */
	public function is_valid_persona( $persona_id ) {
		return $this->persona_manager->is_valid_persona( $persona_id );
	}

	/**
	 * Get all available personas.
	 *
	 * @since     1.1.0
	 * @return    array    Array of available personas in format [id => name].
	 */
	public function get_all_personas() {
		return $this->persona_manager->get_all_personas();
	}

	/**
	 * Get persona details.
	 *
	 * @since     1.1.0
	 * @param     string $persona_id    The persona identifier.
	 * @return    array|null            The persona details, or null if not found.
	 */
	public function get_persona_details( $persona_id ) {
		return $this->persona_manager->get_persona_details( $persona_id );
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
		return $this->persona_content->get_content( $entity_id, $entity_type, $content_field, $persona_id );
	}

	/**
	 * Set persona-specific content for an entity.
	 *
	 * @since     1.1.0
	 * @param     int    $entity_id       The entity ID (e.g., post ID).
	 * @param     string $content_field   The content field name.
	 * @param     mixed  $content         The content to store.
	 * @param     string $persona_id      The persona ID.
	 * @param     string $entity_type     The entity type (default: 'post').
	 * @return    bool                    Whether the content was set successfully.
	 */
	public function set_content( $entity_id, $content_field, $content, $persona_id, $entity_type = 'post' ) {
		return $this->persona_content->set_content( $entity_id, $content_field, $content, $persona_id, $entity_type );
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
		return $this->persona_content->process_block_content( $content, $persona_id );
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
		return $this->persona_content->get_personas_with_content( $entity_id, $entity_type );
	}
}
