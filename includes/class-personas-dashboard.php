<?php
/**
 * Dashboard for CME Personas administration.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Personas Dashboard class.
 *
 * Provides the main dashboard page for the Personas admin section.
 *
 * @since      1.3.0
 * @package    CME_Personas
 */
class Personas_Dashboard {

	/**
	 * Instance of the class.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      Personas_Dashboard    $instance    Singleton instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of the class.
	 *
	 * @since     1.3.0
	 * @return    Personas_Dashboard    The singleton instance.
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
				CME_PERSONAS_URL . 'admin/css/personas-dashboard.css',
				array(),
				CME_PERSONAS_VERSION,
				'all'
			);
		}
	}

	/**
	 * Enqueue dashboard specific scripts.
	 *
	 * @since    1.5.3
	 * @param    string $hook    Current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only load on our dashboard page.
		if ( 'toplevel_page_cme-personas-dashboard' === $hook ) {
			wp_enqueue_script(
				'cme-personas-dashboard',
				CME_PERSONAS_URL . 'admin/js/personas-dashboard.js',
				array( 'jquery' ),
				CME_PERSONAS_VERSION,
				true
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

		// We need to add a callback for removing the default submenu items after they're created.
		// WordPress automatically adds the main menu as a submenu item too, which we don't want.
		add_action( 'admin_menu', array( $this, 'adjust_submenus' ), 999 );

		// Future pages can be added here. Additional submenu items could include
		// settings, help, or analytics pages when implemented in future versions.
		// Note: Other potential admin pages could be added with add_submenu_page(),
		// similar to how a settings page would work with the render_settings_page method.
	}

	/**
	 * Get image filename without extension.
	 *
	 * @param int $attachment_id The attachment ID.
	 * @return string The filename without extension.
	 */
	private function get_image_name( $attachment_id ) {
		if ( empty( $attachment_id ) ) {
			return __( 'Unknown', 'cme-personas' );
		}

		$filename      = basename( get_attached_file( $attachment_id ) );
		$name_parts    = explode( '.', $filename );
		$name_no_ext   = $name_parts[0];
		$name_readable = ucfirst( str_replace( array( '-', '_' ), ' ', $name_no_ext ) );

		// If it's a full path with multiple dots, get only the filename.
		if ( count( $name_parts ) > 2 ) {
			$extension     = array_pop( $name_parts );
			$name_no_ext   = implode( '.', $name_parts );
			$name_readable = ucfirst( str_replace( array( '-', '_' ), ' ', $name_no_ext ) );
		}

		// Use just the first word if the name is multiple words.
		$words = explode( ' ', $name_readable );
		if ( count( $words ) > 0 ) {
			return $words[0];
		}

		return $name_readable;
	}

	/**
	 * Render the dashboard page.
	 *
	 * @since    1.3.0
	 */
	public function render_dashboard_page() {
		// Get all personas.
		$personas = get_posts(
			array(
				'post_type'      => 'persona',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="wrap cme-personas-dashboard">
			<h1><?php esc_html_e( 'Personas Dashboard', 'cme-personas' ); ?></h1>

			<!-- Personas Overview Section -->
			<div class="cme-dashboard-section">
				<h2><?php esc_html_e( 'Personas Overview', 'cme-personas' ); ?></h2>

				<div class="cme-persona-grid">
					<?php if ( $personas ) : ?>
						<?php foreach ( $personas as $persona ) : ?>
							<?php
							// Get persona images.
							$male_image_id          = get_post_meta( $persona->ID, 'persona_image_male', true );
							$female_image_id        = get_post_meta( $persona->ID, 'persona_image_female', true );
							$indeterminate_image_id = get_post_meta( $persona->ID, 'persona_image_indeterminate', true );

							// Get image names.
							$male_name          = $this->get_image_name( $male_image_id );
							$female_name        = $this->get_image_name( $female_image_id );
							$indeterminate_name = $this->get_image_name( $indeterminate_image_id );

							// Get attributes from post content.
							$attributes = $persona->post_content;
							?>
							<div class="cme-persona-card">
								<h2 class="cme-persona-card-title"><?php echo esc_html( $persona->post_title ); ?></h2>

								<div class="cme-persona-image-rotator" role="region" aria-label="<?php esc_attr_e( 'Persona image gallery', 'cme-personas' ); ?>">
									<div class="cme-persona-image-container">
										<?php if ( $male_image_id ) : ?>
											<div class="cme-persona-slide" aria-hidden="false">
												<?php
												// Translators: %s is the name of the persona.
												$male_alt_text = sprintf( esc_attr__( '%s persona image', 'cme-personas' ), esc_attr( $male_name ) );
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image.
												echo wp_get_attachment_image(
													$male_image_id,
													'medium',
													false,
													array(
														'class'   => 'cme-persona-slide-image',
														'alt'     => $male_alt_text,
														'loading' => 'lazy',
													)
												);
												?>
												<div class="cme-persona-slide-caption"><?php echo esc_html( $male_name ); ?></div>
											</div>
										<?php else : ?>
											<div class="cme-persona-slide" aria-hidden="false">
												<div class="cme-persona-image-placeholder dashicons dashicons-businessman" aria-hidden="true"></div>
												<div class="cme-persona-slide-caption"><?php esc_html_e( 'Male', 'cme-personas' ); ?></div>
											</div>
										<?php endif; ?>

										<?php if ( $female_image_id ) : ?>
											<div class="cme-persona-slide" aria-hidden="true">
												<?php
												// Translators: %s is the name of the persona.
												$female_alt_text = sprintf( esc_attr__( '%s persona image', 'cme-personas' ), esc_attr( $female_name ) );
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image.
												echo wp_get_attachment_image(
													$female_image_id,
													'medium',
													false,
													array(
														'class'   => 'cme-persona-slide-image',
														'alt'     => $female_alt_text,
														'loading' => 'lazy',
													)
												);
												?>
												<div class="cme-persona-slide-caption"><?php echo esc_html( $female_name ); ?></div>
											</div>
										<?php else : ?>
											<div class="cme-persona-slide" aria-hidden="true">
												<div class="cme-persona-image-placeholder dashicons dashicons-businesswoman" aria-hidden="true"></div>
												<div class="cme-persona-slide-caption"><?php esc_html_e( 'Female', 'cme-personas' ); ?></div>
											</div>
										<?php endif; ?>

										<?php if ( $indeterminate_image_id ) : ?>
											<div class="cme-persona-slide" aria-hidden="true">
												<?php
												// Translators: %s is the name of the persona.
												$indeterminate_alt_text = sprintf( esc_attr__( '%s persona image', 'cme-personas' ), esc_attr( $indeterminate_name ) );
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image.
												echo wp_get_attachment_image(
													$indeterminate_image_id,
													'medium',
													false,
													array(
														'class'   => 'cme-persona-slide-image',
														'alt'     => $indeterminate_alt_text,
														'loading' => 'lazy',
													)
												);
												?>
												<div class="cme-persona-slide-caption"><?php echo esc_html( $indeterminate_name ); ?></div>
											</div>
										<?php else : ?>
											<div class="cme-persona-slide" aria-hidden="true">
												<div class="cme-persona-image-placeholder dashicons dashicons-admin-users" aria-hidden="true"></div>
												<div class="cme-persona-slide-caption"><?php esc_html_e( 'Person', 'cme-personas' ); ?></div>
											</div>
										<?php endif; ?>
									</div>

									<div class="cme-persona-rotator-nav" aria-label="<?php esc_attr_e( 'Persona image gallery controls', 'cme-personas' ); ?>">
										<button class="cme-persona-rotator-prev" aria-label="<?php esc_attr_e( 'Previous image', 'cme-personas' ); ?>">&lsaquo;</button>
										<div class="cme-persona-rotator-dots" role="tablist" aria-label="<?php esc_attr_e( 'Select a persona image', 'cme-personas' ); ?>"></div>
										<button class="cme-persona-rotator-next" aria-label="<?php esc_attr_e( 'Next image', 'cme-personas' ); ?>">&rsaquo;</button>
									</div>
								</div>

								<div class="cme-persona-attributes">
									<h3 class="cme-persona-attributes-title"><?php esc_html_e( 'Key Attributes:', 'cme-personas' ); ?></h3>
									<?php if ( ! empty( $attributes ) ) : ?>
										<div class="cme-persona-attributes-content">
											<?php echo wp_kses_post( wpautop( $attributes ) ); ?>
										</div>
									<?php else : ?>
										<p class="cme-persona-attributes-content"><?php esc_html_e( 'No attributes defined.', 'cme-personas' ); ?></p>
									<?php endif; ?>
								</div>

								<div class="cme-persona-actions">
									<a href="<?php echo esc_url( get_edit_post_link( $persona->ID ) ); ?>" class="cme-persona-action-edit">
										<?php esc_html_e( 'Edit', 'cme-personas' ); ?>
									</a>
									<a href="<?php echo esc_url( get_permalink( $persona->ID ) ); ?>" class="cme-persona-action-view">
										<?php esc_html_e( 'View', 'cme-personas' ); ?>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php esc_html_e( 'No personas found.', 'cme-personas' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Documentation & Management Section -->
			<div class="cme-dashboard-section">
				<h2><?php esc_html_e( 'Documentation & Management', 'cme-personas' ); ?></h2>

				<div class="cme-personas-dashboard cme-card-grid">
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=persona' ) ); ?>" class="cme-card">
						<div class="cme-card-icon dashicons dashicons-plus-alt"></div>
						<div class="cme-card-title"><?php esc_html_e( 'Add New Persona', 'cme-personas' ); ?></div>
						<div class="cme-card-desc"><?php esc_html_e( 'Create a new persona profile', 'cme-personas' ); ?></div>
					</a>

					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=persona' ) ); ?>" class="cme-card">
						<div class="cme-card-icon dashicons dashicons-admin-generic"></div>
						<div class="cme-card-title"><?php esc_html_e( 'Manage Personas', 'cme-personas' ); ?></div>
						<div class="cme-card-desc"><?php esc_html_e( 'View and edit all personas', 'cme-personas' ); ?></div>
					</a>

					<a href="<?php echo esc_url( plugin_dir_url( CME_PERSONAS_FILE ) . 'docs/guides/PERSONAS.md' ); ?>" class="cme-card">
						<div class="cme-card-icon dashicons dashicons-book"></div>
						<div class="cme-card-title"><?php esc_html_e( 'Using Personas', 'cme-personas' ); ?></div>
						<div class="cme-card-desc"><?php esc_html_e( 'Learn how to implement personas in content', 'cme-personas' ); ?></div>
					</a>

					<a href="<?php echo esc_url( plugin_dir_url( CME_PERSONAS_FILE ) . 'docs/development/' ); ?>" class="cme-card">
						<div class="cme-card-icon dashicons dashicons-editor-code"></div>
						<div class="cme-card-title"><?php esc_html_e( 'Developer Guide', 'cme-personas' ); ?></div>
						<div class="cme-card-desc"><?php esc_html_e( 'Technical documentation for developers', 'cme-personas' ); ?></div>
					</a>
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
