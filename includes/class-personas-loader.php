<?php
/**
 * Personas Loader Class
 *
 * Bootstrap class for initializing the plugin components.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Loader Class
 *
 * This class is responsible for initializing all plugin components,
 * loading required files, and setting up the plugin's infrastructure.
 * It's the main bootstrap mechanism for the plugin.
 *
 * @since      1.6.0
 * @package    CME_Personas
 */
class Personas_Loader {

	/**
	 * Initialize and run the plugin.
	 *
	 * @since    1.6.0
	 */
	public function run() {
		// Load dependencies.
		$this->load_dependencies();

		// Load text domain for internationalization.
		$this->load_textdomain();

		// Initialize core components.
		$this->initialize_components();

		// Set up WordPress hooks and actions.
		$this->setup_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function load_dependencies() {
		// Core components.
		$base_path = plugin_dir_path( __DIR__ ) . 'includes/';

		// Load component files.
		$files = array(
			'class-custom-post-types.php',
			'class-admin.php',
			'class-dashboard.php',
			'class-personas-repository.php',
			'class-personas-storage.php',
			'class-personas-detector.php',
			'class-personas-facade.php',
			'class-personas-assets.php',
			'class-shortcodes.php',
			'class-settings.php',
			'class-ajax-handler.php',
		);

		foreach ( $files as $file ) {
			if ( file_exists( $base_path . $file ) ) {
				require_once $base_path . $file;
			}
		}

		// Load frontend class to ensure shortcodes are registered.
		if ( file_exists( $base_path . 'class-frontend.php' ) ) {
			require_once $base_path . 'class-frontend.php';
		}
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function initialize_components() {
		// Initialize Custom Post Types.
		$custom_post_types = new Custom_Post_Types();
		$custom_post_types->register();

		// Initialize admin components.
		$admin = new Admin();
		$admin->register();

		// Initialize shortcodes.
		$shortcodes = new Shortcodes();
		$shortcodes->register();

		// Initialize settings.
		$settings = new Settings();
		$settings->register();

		// Initialize components that use get_instance().
		Dashboard::get_instance();
		Personas_Repository::get_instance();
		Personas_Storage::get_instance();
		Personas_Detector::get_instance();
		Personas_Facade::get_instance();
		Personas_Assets::get_instance();
		Ajax_Handler::get_instance();

		// Initialize frontend if it exists.
		if ( class_exists( '\\CME_Personas\\Frontend' ) ) {
			Frontend::get_instance();
		}
	}

	/**
	 * Set up hooks.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	private function setup_hooks() {
		// Register AJAX handler for welcome notice.
		add_action( 'wp_ajax_cme_dismiss_welcome', array( $this, 'dismiss_welcome_notice' ) );

		// Add welcome notice.
		add_action( 'admin_notices', array( $this, 'display_welcome_notice' ) );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'cme-personas',
			false,
			dirname( plugin_basename( CME_PERSONAS_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Display welcome notice for first-time users.
	 *
	 * @since    1.6.0
	 * @return   void
	 */
	public function display_welcome_notice() {
		// Only show to admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if we've shown this before.
		if ( get_option( 'cme_cpt_taxonomy_welcome_shown' ) ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible cme-welcome-notice">
			<h3><?php esc_html_e( 'Thank you for installing Cruise Made Easy Personas!', 'cme-personas' ); ?></h3>
			<p><?php esc_html_e( 'You can now start using Customer Personas.', 'cme-personas' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=persona' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Manage Personas', 'cme-personas' ); ?></a>
				<a href="#" class="cme-dismiss-welcome button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'cme_dismiss_welcome' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cme-personas' ); ?></a>
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
	 * @since    1.6.0
	 * @return   void
	 */
	public function dismiss_welcome_notice() {
		if ( ! current_user_can( 'manage_options' ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cme_dismiss_welcome' ) ) {
			wp_send_json_error( 'Permission denied' );
		}
		update_option( 'cme_cpt_taxonomy_welcome_shown', 1 );
		wp_send_json_success();
	}
}
