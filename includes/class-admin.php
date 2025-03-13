<?php
/**
 * Admin functionalities for CME Personas.
 *
 * @since      1.1.0
 * @package    CME_Personas
 */

namespace CME_Personas;

/**
 * Admin class.
 *
 * Provides admin interface functionality for the plugin.
 *
 * @since      1.1.0
 * @package    CME_Personas
 * @author     Your Name
 */
class Admin {

	/**
	 * Initialize the class.
	 *
	 * Register hooks and filters for the admin interface.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register(): void {
		// Enqueue scripts and styles for admin if needed.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles for admin.
	 *
	 * @since    1.0.0
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function enqueue_scripts( string $hook ): void {
		// Enqueue admin CSS for all admin pages.
		wp_enqueue_style(
			'cme-personas-admin',
			plugin_dir_url( CME_PERSONAS_FILE ) . 'admin/css/personas-admin.css',
			array(),
			CME_PERSONAS_VERSION,
			'all'
		);

		// Enqueue dashboard CSS.
		wp_enqueue_style(
			'cme-personas-dashboard',
			plugin_dir_url( CME_PERSONAS_FILE ) . 'admin/css/personas-dashboard.css',
			array(),
			CME_PERSONAS_VERSION,
			'all'
		);

		// Enqueue the admin menu fix script.
		wp_enqueue_script(
			'cme-personas-admin-menu-fix',
			plugin_dir_url( CME_PERSONAS_FILE ) . 'admin/js/admin-menu-fix.js',
			array( 'jquery' ),
			CME_PERSONAS_VERSION,
			true
		);

		// Add dashboard styles for dashboard page.
		if ( 'toplevel_page_cme-personas-dashboard' === $hook ) {
			wp_enqueue_style( 'dashboard' );
			wp_enqueue_script( 'dashboard' );
		}
	}
}
