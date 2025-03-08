<?php
/**
 * Plugin settings page.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */

namespace CME_CPT_Taxonomy;

/**
 * Settings class.
 *
 * This class handles the plugin settings page.
 *
 * @since      1.0.0
 * @package    CME_CPT_Taxonomy
 */
class Settings {

	/**
	 * Option name for settings.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private string $option_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->option_name = 'cme_persona_rotator_settings';
	}

	/**
	 * Register settings page and options.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register(): void {
		// Add settings page
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_settings_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Customer Personas Rotator', 'cme-cpt-and-taxonomy' ),
			__( 'Customer Personas', 'cme-cpt-and-taxonomy' ),
			'manage_options',
			'cme-persona-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register_settings(): void {
		// Register setting
		register_setting(
			'cme_persona_settings',
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'default_limit' => 3,
					'default_speed' => 5000,
				),
			)
		);

		// Add settings section
		add_settings_section(
			'cme_persona_rotator_section',
			__( 'Persona Rotator Settings', 'cme-cpt-and-taxonomy' ),
			array( $this, 'render_section_description' ),
			'cme-persona-settings'
		);

		// Add settings fields
		add_settings_field(
			'default_limit',
			__( 'Default Number of Personas', 'cme-cpt-and-taxonomy' ),
			array( $this, 'render_limit_field' ),
			'cme-persona-settings',
			'cme_persona_rotator_section',
			array( 'label_for' => 'default_limit' )
		);

		add_settings_field(
			'default_speed',
			__( 'Default Rotation Speed (ms)', 'cme-cpt-and-taxonomy' ),
			array( $this, 'render_speed_field' ),
			'cme-persona-settings',
			'cme_persona_rotator_section',
			array( 'label_for' => 'default_speed' )
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @since    1.0.0
	 * @param    array $input  The input array.
	 * @return   array         The sanitized input.
	 */
	public function sanitize_settings( $input ): array {
		$sanitized = array();

		// Sanitize limit
		$sanitized['default_limit'] = isset( $input['default_limit'] ) ?
			absint( $input['default_limit'] ) : 3;

		// Ensure limit is at least 1
		if ( $sanitized['default_limit'] < 1 ) {
			$sanitized['default_limit'] = 1;
		}

		// Sanitize speed
		$sanitized['default_speed'] = isset( $input['default_speed'] ) ?
			absint( $input['default_speed'] ) : 5000;

		// Ensure speed is at least 1000ms (1 second)
		if ( $sanitized['default_speed'] < 1000 ) {
			$sanitized['default_speed'] = 1000;
		}

		return $sanitized;
	}

	/**
	 * Render settings section description.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_section_description(): void {
		?>
		<p><?php esc_html_e( 'Configure default settings for the persona rotator shortcode.', 'cme-cpt-and-taxonomy' ); ?></p>
		<?php
	}

	/**
	 * Render limit field.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_limit_field(): void {
		$options = get_option( $this->option_name );
		$value   = isset( $options['default_limit'] ) ? $options['default_limit'] : 3;
		?>
		<input type="number"
				id="default_limit"
				name="<?php echo esc_attr( $this->option_name ); ?>[default_limit]"
				value="<?php echo esc_attr( $value ); ?>"
				min="1"
				step="1"
				class="small-text">
		<p class="description">
			<?php esc_html_e( 'The default number of personas to display in the rotator.', 'cme-cpt-and-taxonomy' ); ?>
		</p>
		<?php
	}

	/**
	 * Render speed field.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_speed_field(): void {
		$options = get_option( $this->option_name );
		$value   = isset( $options['default_speed'] ) ? $options['default_speed'] : 5000;
		?>
		<input type="number"
				id="default_speed"
				name="<?php echo esc_attr( $this->option_name ); ?>[default_speed]"
				value="<?php echo esc_attr( $value ); ?>"
				min="1000"
				step="100"
				class="small-text">
		<p class="description">
			<?php esc_html_e( 'The default rotation speed in milliseconds (1000 = 1 second).', 'cme-cpt-and-taxonomy' ); ?>
		</p>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'cme_persona_settings' );
				do_settings_sections( 'cme-persona-settings' );
				submit_button();
				?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Using the Customer Persona Rotator', 'cme-cpt-and-taxonomy' ); ?></h2>

			<div class="cme-settings-documentation">
				<h3><?php esc_html_e( 'Basic Usage', 'cme-cpt-and-taxonomy' ); ?></h3>
				<p><?php esc_html_e( 'To display the persona rotator, add this shortcode to any page or post:', 'cme-cpt-and-taxonomy' ); ?></p>
				<pre><code>[cme-persona-rotator]</code></pre>

				<h3><?php esc_html_e( 'Advanced Usage', 'cme-cpt-and-taxonomy' ); ?></h3>
				<p><?php esc_html_e( 'You can customize the rotator with these parameters:', 'cme-cpt-and-taxonomy' ); ?></p>
				<ul>
					<li><strong>limit</strong>: <?php esc_html_e( 'Number of personas to display (default: value set above)', 'cme-cpt-and-taxonomy' ); ?></li>
					<li><strong>speed</strong>: <?php esc_html_e( 'Rotation speed in milliseconds (default: value set above)', 'cme-cpt-and-taxonomy' ); ?></li>
				</ul>

				<p><?php esc_html_e( 'Example with parameters:', 'cme-cpt-and-taxonomy' ); ?></p>
				<pre><code>[cme-persona-rotator limit="5" speed="3000"]</code></pre>

				<h3><?php esc_html_e( 'How It Works', 'cme-cpt-and-taxonomy' ); ?></h3>
				<p><?php esc_html_e( 'The rotator randomly selects personas from your collection and displays them in rotation. For each persona, it randomly selects one gender-specific image (male, female, or indeterminate).', 'cme-cpt-and-taxonomy' ); ?></p>
				<p><?php esc_html_e( 'To add gender-specific images to a persona, edit the persona and scroll down to the "Gender-Specific Images" section.', 'cme-cpt-and-taxonomy' ); ?></p>

				<h3><?php esc_html_e( 'Best Practices', 'cme-cpt-and-taxonomy' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Use images of the same dimensions for all personas to maintain consistent appearance.', 'cme-cpt-and-taxonomy' ); ?></li>
					<li><?php esc_html_e( 'Keep persona excerpts concise for better readability in the rotator overlay.', 'cme-cpt-and-taxonomy' ); ?></li>
					<li><?php esc_html_e( 'For optimal performance, don\'t set the limit too high if you have many personas.', 'cme-cpt-and-taxonomy' ); ?></li>
				</ul>
			</div>

			<style>
				.cme-settings-documentation {
					background: #fff;
					border: 1px solid #ccd0d4;
					padding: 15px 20px;
					margin: 15px 0;
				}
				.cme-settings-documentation h3 {
					margin-top: 20px;
					margin-bottom: 10px;
					padding-bottom: 5px;
					border-bottom: 1px solid #eee;
				}
				.cme-settings-documentation pre {
					background: #f5f5f5;
					padding: 10px;
					border: 1px solid #ddd;
					overflow: auto;
				}
				.cme-settings-documentation code {
					font-size: 13px;
				}
				.cme-settings-documentation ul {
					list-style: disc;
					margin-left: 20px;
				}
			</style>
		</div>
		<?php
	}

	/**
	 * Get settings.
	 *
	 * @since    1.0.0
	 * @return   array  The settings.
	 */
	public function get_settings(): array {
		$defaults = array(
			'default_limit' => 3,
			'default_speed' => 5000,
		);

		$options = get_option( $this->option_name, $defaults );

		return $options;
	}
}
