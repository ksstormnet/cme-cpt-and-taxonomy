<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for Persona functionality.
 *
 * @since      1.2.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * AJAX Handler Class
 *
 * This class handles all AJAX requests for the Persona system.
 *
 * @since      1.2.0
 * @package    CME_Personas
 */
class Ajax_Handler {

	/**
	 * Instance of the class.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      Ajax_Handler    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.2.0
	 * @return    Ajax_Handler    The singleton instance.
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
	 * @since    1.2.0
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.2.0
	 */
	private function setup_hooks() {
		// Register AJAX handlers.
		add_action( 'wp_ajax_cme_preview_persona_content', array( $this, 'preview_persona_content' ) );
		add_action( 'wp_ajax_cme_check_persona_content', array( $this, 'check_persona_content' ) );

		// Frontend AJAX handlers for both logged-in and non-logged-in users.
		add_action( 'wp_ajax_cme_get_persona_content', array( $this, 'get_persona_content' ) );
		add_action( 'wp_ajax_nopriv_cme_get_persona_content', array( $this, 'get_persona_content' ) );
	}

	/**
	 * Get persona-specific content via AJAX.
	 *
	 * @since    1.4.0
	 */
	public function get_persona_content() {
		// Check nonce.
		if ( ! check_ajax_referer( 'cme_personas_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'cme-personas' ) ) );
		}

		// Get parameters.
		$post_id  = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$field    = isset( $_POST['field'] ) ? sanitize_key( $_POST['field'] ) : 'content';
		$persona  = isset( $_POST['persona'] ) ? sanitize_key( $_POST['persona'] ) : '';

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'cme-personas' ) ) );
		}

		// Get content manager.
		$content_manager = Persona_Content::get_instance();
		$api = Personas_API::get_instance();

		// Get the content.
		$content = $api->get_content( $post_id, 'post', $field, $persona );

		// Return result.
		wp_send_json_success(
			array(
				'content'  => $content,
				'field'    => $field,
				'persona'  => $persona,
			)
		);
	}

	/**
	 * Check if persona content exists for a post.
	 *
	 * @since    1.2.0
	 */
	public function check_persona_content() {
		// Check nonce.
		if ( ! check_ajax_referer( 'cme_personas_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'cme-personas' ) ) );
		}

		// Check if user has permission.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'cme-personas' ) ) );
		}

		// Get parameters.
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$persona = isset( $_POST['persona'] ) ? sanitize_key( $_POST['persona'] ) : '';

		if ( empty( $post_id ) || empty( $persona ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'cme-personas' ) ) );
		}

		// Get post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'cme-personas' ) ) );
		}

		// Get content manager.
		$content_manager = Persona_Content::get_instance();
		$has_content     = false;

		// Check if there's any content for this persona.
		$variations = $content_manager->get_content_variations( $post_id, 'post', $persona );
		if ( ! empty( $variations ) ) {
			// Check if any of the variation fields have content.
			$has_content = ! empty( $variations['title'] ) || ! empty( $variations['content'] ) || ! empty( $variations['excerpt'] );
		}

		// Return result.
		wp_send_json_success( array( 'hasContent' => $has_content ) );
	}

	/**
	 * Preview persona-specific content.
	 *
	 * @since    1.2.0
	 */
	public function preview_persona_content() {
		// Check nonce.
		if ( ! check_ajax_referer( 'cme_personas_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'cme-personas' ) ) );
		}

		// Check if user has permission.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'cme-personas' ) ) );
		}

		// Get parameters.
		$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$persona = isset( $_POST['persona'] ) ? sanitize_key( $_POST['persona'] ) : '';

		if ( empty( $post_id ) || empty( $persona ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'cme-personas' ) ) );
		}

		// Get post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'cme-personas' ) ) );
		}

		// Get content manager.
		$content_manager = Persona_Content::get_instance();
		$persona_content = $content_manager->get_content( $post_id, 'post', 'content', $persona );
		$persona_title   = $content_manager->get_content( $post_id, 'post', 'title', $persona );
		$persona_excerpt = $content_manager->get_content( $post_id, 'post', 'excerpt', $persona );

		// Build preview HTML.
		$preview_html = '<div class="cme-persona-preview">';
		$preview_html .= '<h2>' . esc_html( $persona_title ) . '</h2>';

		if ( ! empty( $persona_excerpt ) ) {
			$preview_html .= '<div class="cme-persona-excerpt">';
			$preview_html .= '<h3>' . esc_html__( 'Excerpt', 'cme-personas' ) . '</h3>';
			$preview_html .= '<div class="cme-persona-excerpt-content">' . wp_kses_post( $persona_excerpt ) . '</div>';
			$preview_html .= '</div>';
		}

		$preview_html .= '<div class="cme-persona-content">';
		$preview_html .= '<h3>' . esc_html__( 'Content', 'cme-personas' ) . '</h3>';
		$preview_html .= '<div class="cme-persona-content-wrapper">' . wp_kses_post( $persona_content ) . '</div>';
		$preview_html .= '</div>';
		$preview_html .= '</div>';

		wp_send_json_success( $preview_html );
	}
}
