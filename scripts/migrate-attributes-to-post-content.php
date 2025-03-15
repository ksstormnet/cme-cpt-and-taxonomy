<?php
/**
 * Migration script to move persona attributes from meta field to post content.
 *
 * This script should be run once after updating the plugin to move existing
 * persona attributes from the custom meta field to the standard post_content field.
 *
 * Usage:
 * 1. Place this file in your WordPress root directory
 * 2. Run via WP-CLI: wp eval-file migrate-attributes-to-post-content.php
 * 3. Or run via browser (with proper access control)
 *
 * @package CME_Personas
 */

// Verify this is being run in WordPress context.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
    // Bootstrap WordPress.
    $wp_load_path = dirname( __FILE__ ) . '/wp-load.php';
    if ( file_exists( $wp_load_path ) ) {
        require_once $wp_load_path;
    } else {
        die( 'Cannot find WordPress installation. Please run this from the WordPress root directory.' );
    }
}

// Verify user has permission.
if ( ! current_user_can( 'manage_options' ) && ! defined( 'WP_CLI' ) ) {
    die( 'You do not have sufficient permissions to run this script.' );
}

/**
 * Migrate all persona attributes from meta field to post content.
 */
function migrate_persona_attributes() {
    $migrated = 0;
    $skipped = 0;
    $errors = 0;

    // Get all persona posts.
    $personas = get_posts(
        array(
            'post_type'      => 'persona',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        )
    );

    if ( empty( $personas ) ) {
        log_message( 'No personas found to migrate.' );
        return;
    }

    log_message( sprintf( 'Found %d personas to process.', count( $personas ) ) );

    foreach ( $personas as $persona ) {
        // Skip if the post content is not empty, to avoid overwriting data.
        if ( ! empty( $persona->post_content ) ) {
            log_message( sprintf( 'Skipping persona #%d "%s" - post content is not empty.', $persona->ID, $persona->post_title ) );
            $skipped++;
            continue;
        }

        // Get attributes from meta.
        $attributes = get_post_meta( $persona->ID, 'persona_attributes', true );

        // Skip if no attributes found.
        if ( empty( $attributes ) && ! is_numeric( $attributes ) ) {
            log_message( sprintf( 'Skipping persona #%d "%s" - no attributes found.', $persona->ID, $persona->post_title ) );
            $skipped++;
            continue;
        }

        // Update post content.
        $updated_post = array(
            'ID'           => $persona->ID,
            'post_content' => $attributes,
        );

        $result = wp_update_post( $updated_post );

        if ( is_wp_error( $result ) ) {
            log_message( sprintf( 'Error updating persona #%d "%s": %s', $persona->ID, $persona->post_title, $result->get_error_message() ) );
            $errors++;
        } else {
            log_message( sprintf( 'Successfully migrated attributes for persona #%d "%s".', $persona->ID, $persona->post_title ) );
            $migrated++;
        }
    }

    log_message( sprintf( 'Migration complete. %d personas migrated, %d skipped, %d errors.', $migrated, $skipped, $errors ) );
}

/**
 * Log a message to the appropriate output.
 *
 * @param string $message The message to log.
 */
function log_message( $message ) {
    if ( defined( 'WP_CLI' ) ) {
        WP_CLI::line( $message );
    } else {
        echo $message . '<br>';
    }
}

// Run the migration.
log_message( 'Starting migration of persona attributes to post content...' );
migrate_persona_attributes();
log_message( 'Done.' );
