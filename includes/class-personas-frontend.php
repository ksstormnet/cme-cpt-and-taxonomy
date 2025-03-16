<?php
/**
 * Personas Frontend Integration Class
 *
 * Handles frontend functionality for Persona content.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Frontend Integration Class
 *
 * This class provides shortcodes, template functions, and frontend persona
 * switching functionality for the Personas plugin using a boundary-based approach.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */
class Personas_Frontend {

	/**
	 * Instance of the class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Personas_Frontend    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance of the Personas_Detector class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Detector    $detector    Instance of the Personas_Detector class.
	 */
	private $detector;

	/**
	 * Instance of the Personas_Facade class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Facade    $facade    Instance of the Personas_Facade class.
	 */
	private $facade;

	/**
	 * Instance of the Personas_Repository class.
	 *
	 * @since    1.6.0
	 * @access   private
	 * @var      Personas_Repository    $repository    Instance of the Personas_Repository class.
	 */
	private $repository;

	/**
	 * Shortcode nesting level tracking.
	 *
	 * @since    1.5.0
	 * @access   private
	 * @var      int    $nesting_level    Current nesting level of shortcodes.
	 */
	private $nesting_level = 0;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.3.0
	 * @return    Personas_Frontend    The singleton instance.
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
		$this->detector   = Personas_Detector::get_instance();
		$this->facade     = Personas_Facade::get_instance();
		$this->repository = Personas_Repository::get_instance();

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
		add_shortcode( 'if_persona', array( $this, 'if_persona_shortcode' ) );

		// Add frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Filter main content to optimize shortcode processing.
		add_filter( 'the_content', array( $this, 'maybe_process_persona_shortcodes' ), 11 );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since    1.3.0
	 */
	public function enqueue_frontend_assets() {
		// Only load assets if they haven't been loaded yet.
		if ( ! wp_script_is( 'cme-personas-frontend', 'enqueued' ) ) {
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
					'currentPersona' => $this->facade->get_current_persona(),
					'reloadOnSwitch' => apply_filters( 'cme_personas_reload_on_switch', false ),
				)
			);
		}
	}

	/**
	 * Process content with shortcodes safely.
	 *
	 * This method handles general shortcode processing in content
	 * with protection against excessive nesting.
	 *
	 * @since     1.4.2
	 * @param     string $content   Content to process.
	 * @return    string            Processed content.
	 */
	private function process_shortcodes( $content ) {
		if ( ! empty( $content ) ) {
			// Prevent excessive nesting.
			if ( $this->nesting_level > 10 ) {
				return $content; // Too deep, just return unprocessed.
			}

			++$this->nesting_level;
			$processed = do_shortcode( $content );
			--$this->nesting_level;

			return $processed;
		}
		return $content;
	}

	/**
	 * Conditionally process shortcodes in content for performance optimization.
	 *
	 * @since     1.5.0
	 * @param     string $content   The content to process.
	 * @return    string            The processed content.
	 */
	public function maybe_process_persona_shortcodes( $content ) {
		// Only process if shortcodes exist in the content.
		if ( false !== strpos( $content, '[if_persona' ) ) {
			// Reset nesting level counter.
			$this->nesting_level = 0;

			// Add special handling to optimize any persona shortcodes that won't apply.
			$current_persona = $this->facade->get_current_persona();

			// Add filter to quickly handle persona shortcodes.
			add_filter( 'pre_do_shortcode_tag', array( $this, 'pre_process_persona_shortcode' ), 10, 4 );

			// Process the shortcodes.
			$content = do_shortcode( $content );

			// Remove the filter.
			remove_filter( 'pre_do_shortcode_tag', array( $this, 'pre_process_persona_shortcode' ), 10 );
		}

		return $content;
	}

	/**
	 * Pre-process persona shortcodes to quickly exclude non-matching content.
	 *
	 * @since     1.5.0
	 * @param     bool|string $return_value Short-circuit return value.
	 * @param     string      $tag          Shortcode name.
	 * @param     array       $attr         Shortcode attributes.
	 * @param     array       $m            Regular expression match array (unused).
	 * @return    bool|string               Short-circuit return value.
	 */
	public function pre_process_persona_shortcode( $return_value, $tag, $attr, $m ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Only handle our shortcode.
		if ( 'if_persona' !== $tag ) {
			return $return_value;
		}

		// Current persona.
		$current_persona = $this->facade->get_current_persona();

		// Check for 'is' condition.
		if ( isset( $attr['is'] ) ) {
			$allowed_personas = array_map( 'trim', explode( ',', $attr['is'] ) );
			if ( ! in_array( $current_persona, $allowed_personas, true ) ) {
				return ''; // Short-circuit - this content won't be displayed.
			}
		}

		// Check for 'not' condition.
		if ( isset( $attr['not'] ) ) {
			$excluded_personas = array_map( 'trim', explode( ',', $attr['not'] ) );
			if ( in_array( $current_persona, $excluded_personas, true ) ) {
				return ''; // Short-circuit - this content won't be displayed.
			}
		}

		// Let normal processing happen.
		return $return_value;
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
		$current_persona = $this->facade->get_current_persona();

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
		return $this->process_shortcodes( $content );
	}
}
