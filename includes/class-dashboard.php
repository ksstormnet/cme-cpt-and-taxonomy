<?php
/**
 * Dashboard for CME Personas administration.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Dashboard class.
 *
 * Provides the main dashboard page for the Personas admin section.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */
class Dashboard {

	/**
	 * Instance of the class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Dashboard    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.3.0
	 * @return    Dashboard    The singleton instance.
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
		add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Enqueue dashboard specific styles.
	 *
	 * @since    1.3.0
	 * @param    string $hook    Current admin page.
	 */
	public function enqueue_styles( $hook ) {
		// Only load on our dashboard page.
		if ( 'toplevel_page_cme-personas-dashboard' === $hook ) {
			wp_enqueue_style(
				'cme-personas-dashboard',
				CME_PERSONAS_URL . 'admin/css/dashboard.css',
				array(),
				CME_PERSONAS_VERSION,
				'all'
			);
		}
	}

	/**
	 * Register the dashboard page.
	 *
	 * @since    1.3.0
	 */
	public function register_dashboard_page() {
		// Add top level menu page.
		add_menu_page(
			__( 'Personas Dashboard', 'cme-personas' ),
			__( 'Persona Dashboard', 'cme-personas' ),
			'manage_options',
			'cme-personas-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-groups',
			25
		);

		// We need to add a callback for removing the default submenu items after they're created
		// WordPress automatically adds the main menu as a submenu item too, which we don't want
		add_action( 'admin_menu', array( $this, 'adjust_submenus' ), 999 );

		// Future pages can be added here. Additional submenu items could include
		// settings, help, or analytics pages when implemented in future versions.
		// Note: Other potential admin pages could be added with add_submenu_page(),
		// similar to how a settings page would work with the render_settings_page method.
	}

	/**
	 * Render the dashboard page.
	 *
	 * @since    1.3.0
	 */
	public function render_dashboard_page() {
		// Get counts.
		$persona_count = wp_count_posts( 'persona' )->publish;

		// Get recent personas.
		$recent_personas = get_posts(
			array(
				'post_type'      => 'persona',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		?>
		<div class="wrap cme-personas-dashboard">
			<h1><?php esc_html_e( 'Personas Dashboard', 'cme-personas' ); ?></h1>

			<div class="welcome-panel">
				<div class="welcome-panel-content">
					<h2><?php esc_html_e( 'Welcome to Personas!', 'cme-personas' ); ?></h2>
					<p class="about-description"><?php esc_html_e( 'Personas allows you to personalize content for different customer segments.', 'cme-personas' ); ?></p>
					<div class="welcome-panel-column-container">
						<div class="welcome-panel-column">
							<h3><?php esc_html_e( 'Get Started', 'cme-personas' ); ?></h3>
							<ul>
								<li><?php printf( '<a href="%s" class="button button-primary button-hero">%s</a>', esc_url( admin_url( 'edit.php?post_type=persona' ) ), esc_html__( 'Manage Personas', 'cme-personas' ) ); ?></li>
							</ul>
						</div>
						<div class="welcome-panel-column">
							<h3><?php esc_html_e( 'Documentation', 'cme-personas' ); ?></h3>
							<ul>
								<li><a href="#"><?php esc_html_e( 'Using Personas', 'cme-personas' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Shortcodes Reference', 'cme-personas' ); ?></a></li>
								<li><a href="#"><?php esc_html_e( 'Developer Guide', 'cme-personas' ); ?></a></li>
							</ul>
						</div>
						<div class="welcome-panel-column welcome-panel-last">
							<h3><?php esc_html_e( 'Next Steps', 'cme-personas' ); ?></h3>
							<ul>
								<li><a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=persona' ) ); ?>"><?php esc_html_e( 'Create a new persona', 'cme-personas' ); ?></a></li>
								<li><a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Add personalized content to a post', 'cme-personas' ); ?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<div class="metabox-holder">
				<div class="postbox-container" style="width:49%; float:left; margin-right: 2%;">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'At a Glance', 'cme-personas' ); ?></span></h2>
						<div class="inside">
							<div class="main">
								<ul>
									<li class="persona-count">
										<?php
										echo wp_kses(
											sprintf(
												/* translators: %s: number of personas */
												_n( '%s Persona', '%s Personas', $persona_count, 'cme-personas' ),
												'<span class="count">' . esc_html( number_format_i18n( $persona_count ) ) . '</span>'
											),
											array( 'span' => array( 'class' => array() ) )
										);
										?>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div class="postbox-container" style="width:49%; float:left;">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Recent Personas', 'cme-personas' ); ?></span></h2>
						<div class="inside">
							<?php if ( $recent_personas ) : ?>
								<ul>
									<?php foreach ( $recent_personas as $persona ) : ?>
										<li>
											<a href="<?php echo esc_url( get_edit_post_link( $persona->ID ) ); ?>">
												<?php echo esc_html( $persona->post_title ); ?>
											</a>
											<span class="post-date">
												<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $persona->post_date ) ) ); ?>
											</span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php else : ?>
								<p><?php esc_html_e( 'No personas found.', 'cme-personas' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Future method for settings page.
	 *
	 * @since    1.3.0
	 */
	public function render_settings_page() {
		// Will be implemented in future versions.
	}

	/**
	 * Adjust submenu items to remove all automatically created items.
	 *
	 * @since    1.3.0
	 */
	public function adjust_submenus() {
		global $submenu;

		// Remove all submenu items under our page.
		if ( isset( $submenu['cme-personas-dashboard'] ) ) {
			unset( $submenu['cme-personas-dashboard'] );
		}
	}
}
// Additional comment.
