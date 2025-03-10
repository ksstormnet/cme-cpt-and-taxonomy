<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */
class Plugin {

	/**
	 * The custom post types manager instance.
	 *
	 * @since    1.0.0
	 * @var      Custom_Post_Types
	 */
	private readonly Custom_Post_Types $custom_post_types;

	/**
	 * The taxonomies manager instance.
	 *
	 * @since    1.0.0
	 * @var      Taxonomies
	 */
	private readonly Taxonomies $taxonomies;

	/**
	 * The admin enhancements instance.
	 *
	 * @since    1.0.0
	 * @var      Admin
	 */
	private readonly Admin $admin;

	/**
	 * The shortcodes manager instance.
	 *
	 * @since    1.0.0
	 * @var      Shortcodes
	 */
	private readonly Shortcodes $shortcodes;

	/**
	 * The settings page instance.
	 *
	 * @since    1.0.0
	 * @var      Settings
	 */
	private readonly Settings $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->initialize_components();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	private function load_dependencies(): void {
		require_once CME_PLUGIN_DIR . 'includes/class-custom-post-types.php';
		require_once CME_PLUGIN_DIR . 'includes/class-taxonomies.php';
		require_once CME_PLUGIN_DIR . 'includes/class-admin.php';
		require_once CME_PLUGIN_DIR . 'includes/class-shortcodes.php';
		require_once CME_PLUGIN_DIR . 'includes/class-settings.php';
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	private function initialize_components(): void {
		$this->custom_post_types = new Custom_Post_Types();
		$this->taxonomies        = new Taxonomies();
		$this->admin             = new Admin();
		$this->shortcodes        = new Shortcodes();
		$this->settings          = new Settings();
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'cme-cpt-and-taxonomy',
			false,
			dirname( plugin_basename( CME_PLUGIN_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Run the plugin.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function run(): void {
		$this->load_textdomain();
		$this->custom_post_types->register();
		$this->taxonomies->register();
		$this->admin->register();
		$this->shortcodes->register();
		$this->settings->register();

		// Register AJAX handler for getting attachment terms
		add_action( 'wp_ajax_get_attachment_terms', array( $this, 'get_attachment_terms' ) );

		// Register AJAX handler for welcome notice
		add_action( 'wp_ajax_cme_dismiss_welcome', array( $this, 'dismiss_welcome_notice' ) );

		// Add welcome notice
		add_action( 'admin_notices', array( $this, 'display_welcome_notice' ) );
	}

	/**
	 * AJAX handler for getting attachment terms.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function get_attachment_terms(): void {
		// Check nonce for security
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'media-tags-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		// Get attachment ID
		$attachment_id = isset( $_GET['attachment_id'] ) ? intval( $_GET['attachment_id'] ) : 0;
		if ( ! $attachment_id ) {
			wp_send_json_error( 'Invalid attachment ID' );
		}

		// Get taxonomy
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( $_GET['taxonomy'] ) : 'media_tag';

		// Get terms
		$terms = get_the_terms( $attachment_id, $taxonomy );

		$formatted_terms = array();
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$formatted_terms[] = array(
					'term_id' => $term->term_id,
					'name'    => $term->name,
					'slug'    => $term->slug,
				);
			}
		}

		wp_send_json_success(
			array(
				'terms' => $formatted_terms,
			)
		);
	}

	/**
	 * Display welcome notice for first-time users.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_welcome_notice(): void {
		// Only show to admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if we've shown this before
		if ( get_option( 'cme_cpt_taxonomy_welcome_shown' ) ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible cme-welcome-notice">
			<h3><?php esc_html_e( 'Thank you for installing Cruise Made Easy Post Types and Media Tags!', 'cme-cpt-and-taxonomy' ); ?></h3>
			<p><?php esc_html_e( 'You can now start using Customer Personas and organize your Media Library with tags.', 'cme-cpt-and-taxonomy' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=persona' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Manage Personas', 'cme-cpt-and-taxonomy' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>" class="button"><?php esc_html_e( 'Go to Media Library', 'cme-cpt-and-taxonomy' ); ?></a>
				<a href="#" class="cme-dismiss-welcome button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'cme_dismiss_welcome' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cme-cpt-and-taxonomy' ); ?></a>
			</p>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.cme-dismiss-welcome').on('click', function(e) {
					e.preventDefault();
					$.post(ajaxurl, {
						action: 'cme_dismiss_welcome',
						nonce: $(this).data('nonce')
					});
					$(this).closest('.notice').fadeOut();
				});
			});
		</script>
		<?php
	}

	/**
	 * AJAX handler for dismissing welcome notice.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function dismiss_welcome_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cme_dismiss_welcome' ) ) {
			wp_send_json_error( 'Permission denied' );
		}
		update_option( 'cme_cpt_taxonomy_welcome_shown', 1 );
		wp_send_json_success();
	}
}
