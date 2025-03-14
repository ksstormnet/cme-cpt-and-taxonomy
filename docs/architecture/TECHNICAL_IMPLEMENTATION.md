# Technical Implementation Details for Shortcode-Based Persona System

This document provides developers with specific code patterns, hooks, and examples for implementing and extending the persona shortcode system.

## Table of Contents

- [Technical Implementation Details for Shortcode-Based Persona System](#technical-implementation-details-for-shortcode-based-persona-system)
  - [Table of Contents](#table-of-contents)
  - [System Components](#system-components)
  - [Shortcode Implementation](#shortcode-implementation)
    - [Core Shortcode: `[if_persona]`](#core-shortcode-if_persona)
    - [Safe Content Processing](#safe-content-processing)
  - [Persona Detection](#persona-detection)
    - [Detection Logic](#detection-logic)
    - [Setting the Persona](#setting-the-persona)
  - [Storage Method](#storage-method)
  - [Integration Points](#integration-points)
  - [Code Examples](#code-examples)
    - [Shortcode Usage](#shortcode-usage)
    - [Creating and Managing Personas](#creating-and-managing-personas)
    - [Retrieving Persona Data](#retrieving-persona-data)
    - [Extending the System](#extending-the-system)
  - [Performance Considerations](#performance-considerations)
    - [Shortcode Processing Optimization](#shortcode-processing-optimization)
    - [Asset Loading](#asset-loading)
  - [Admin Integration](#admin-integration)
    - [Admin Preview](#admin-preview)
    - [TinyMCE Integration](#tinymce-integration)
  - [Edge Cases and Error Handling](#edge-cases-and-error-handling)
    - [Invalid Persona Handling](#invalid-persona-handling)
    - [Shortcode Nesting Protection](#shortcode-nesting-protection)

## System Components

The persona content system consists of the following core components:

1. **Custom Post Types** - Handles registration of the Persona post type
2. **Shortcode Processing** - Processes the boundary-based shortcodes
3. **Persona Detection** - Identifies and tracks the current persona
4. **Admin Integration** - Tools for previewing and testing content

## Shortcode Implementation

### Core Shortcode: `[if_persona]`

The primary shortcode implementation is found in `includes/class-frontend.php`:

```php
/**
 * Shortcode for conditional persona content.
 *
 * Usage: [if_persona is="business"]Business-specific content[/if_persona]
 *        [if_persona is="family,luxury"]Content for family and luxury personas[/if_persona]
 *        [if_persona not="business"]Content for non-business personas[/if_persona]
 *
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
    $current_persona = $this->personas_api->get_current_persona();

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
```

### Safe Content Processing

To ensure compatibility with third-party shortcodes, a special processing method handles shortcode execution:

```php
/**
 * Process content with shortcodes safely.
 *
 * This method handles shortcode processing in a way that doesn't interfere
 * with third-party shortcodes.
 *
 * @param     string $content   Content to process.
 * @return    string            Processed content.
 */
private function process_shortcodes( $content ) {
    if ( ! empty( $content ) ) {
        return do_shortcode( $content );
    }
    return $content;
}
```

## Persona Detection

### Detection Logic

The system detects the current persona through several methods:

```php
/**
 * Get the current persona.
 *
 * @return    string    The current persona identifier.
 */
public function get_current_persona() {
    // Check query parameter first (highest priority)
    if ( isset( $_GET['persona'] ) ) {
        $persona = sanitize_key( $_GET['persona'] );
        if ( $this->is_valid_persona( $persona ) ) {
            return $persona;
        }
    }

    // Check cookie next
    if ( isset( $_COOKIE['cme_current_persona'] ) ) {
        $persona = sanitize_key( $_COOKIE['cme_current_persona'] );
        if ( $this->is_valid_persona( $persona ) ) {
            return $persona;
        }
    }

    // Fall back to default
    return 'default';
}
```

### Setting the Persona

```php
/**
 * Set the active persona.
 *
 * @param     string $persona_id    The persona identifier to set.
 * @param     bool   $set_cookie    Whether to set a cookie for the persona.
 * @return    bool                  Whether the persona was set successfully.
 */
public function set_persona( $persona_id, $set_cookie = true ) {
    if ( ! $this->is_valid_persona( $persona_id ) ) {
        return false;
    }

    if ( $set_cookie ) {
        setcookie(
            'cme_current_persona',
            $persona_id,
            time() + WEEK_IN_SECONDS,
            COOKIEPATH,
            COOKIE_DOMAIN
        );
    }

    return true;
}
```

## Storage Method

The persona system uses WordPress post types for persona definition:

1. **Post Type**: 'persona' - Stores basic persona information
2. **Content Management**: Uses standard WordPress content with `[if_persona]` shortcodes embedded

The boundary-based shortcode approach doesn't require specialized content storage - regular WordPress content contains the shortcodes that define which sections are visible to which personas.

## Integration Points

The following integration points are available:

1. **API Functions**:

```php
// Get current persona
function cme_get_current_persona();

// Set active persona
function cme_set_persona($persona_id);

// Check if a persona is valid
function cme_is_valid_persona($persona_id);

// Get all available personas
function cme_get_all_personas();
```

2. **Hooks**:

```php
// Filter the detected persona
add_filter('cme_current_persona', function($persona) {
    // Custom logic
    return $persona;
});

// Action when persona is switched
add_action('cme_persona_switched', function($new_persona, $old_persona) {
    // Custom logic
});
```

## Code Examples

### Shortcode Usage

Basic conditional content:

```php
// In a template file or content area:
echo do_shortcode('[if_persona is="easy-breezy"]
    <h2>Easy Cruising Options</h2>
    <p>Simple, straightforward cruise options...</p>
[/if_persona]');
```

Advanced usage with nested shortcodes:

```php
// In a template file:
echo do_shortcode('[if_persona not="thrill"]
    <div class="relaxation-options">
        <h2>Relaxation Options</h2>

        [if_persona is="luxe"]
            <div class="premium-options">
                <h3>Premium Relaxation</h3>
                <!-- Premium content here -->
            </div>
        [/if_persona]

        <div class="standard-options">
            <!-- Standard content here -->
        </div>
    </div>
[/if_persona]');
```

### Creating and Managing Personas

The plugin provides a custom post type for persona management that can be accessed through the WordPress admin interface. Each persona is a custom post with specific meta data:

```php
// Example of creating a persona programmatically
$persona_id = wp_insert_post(array(
    'post_type' => 'persona',
    'post_title' => 'Easy-Breezy Cruiser',
    'post_status' => 'publish',
    'post_excerpt' => 'Casual cruisers looking for relaxation and simplicity',
));

// Add gender-specific images
update_post_meta($persona_id, 'persona_image_male', $male_image_id);
update_post_meta($persona_id, 'persona_image_female', $female_image_id);
update_post_meta($persona_id, 'persona_image_indeterminate', $neutral_image_id);
```

### Retrieving Persona Data

To work with personas in your theme or plugin:

```php
// Get all published personas
$personas = get_posts(array(
    'post_type' => 'persona',
    'post_status' => 'publish',
    'numberposts' => -1,
));

// Get a specific persona
$persona = get_post($persona_id);

// Get gender-specific image
$male_image_id = get_post_meta($persona_id, 'persona_image_male', true);
$male_image_url = wp_get_attachment_image_url($male_image_id, 'full');
```

### Extending the System

Custom functionality can be added using WordPress filters and actions:

```php
// Add a custom persona detection method
add_filter('cme_current_persona', function($persona) {
    // Example: Use logged-in user meta to determine persona
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $user_persona = get_user_meta($user_id, 'preferred_persona', true);

        if (!empty($user_persona)) {
            return $user_persona;
        }
    }

    return $persona;
});

// Track persona changes
add_action('cme_persona_switched', function($new_persona, $old_persona) {
    // Example: Log persona changes to analytics
    if (function_exists('analytics_track_event')) {
        analytics_track_event('persona_changed', array(
            'from' => $old_persona,
            'to' => $new_persona,
            'page' => get_the_ID(),
        ));
    }
});
```

## Performance Considerations

### Shortcode Processing Optimization

The shortcode approach is optimized for performance:

```php
// Check for existence of shortcodes before processing
function maybe_process_persona_shortcodes($content) {
    // Only process if shortcodes exist in the content
    if (strpos($content, '[if_persona') !== false ||
        strpos($content, '[persona_') !== false) {
        return do_shortcode($content);
    }

    return $content;
}
```

### Asset Loading

Conditionally load assets only when needed:

```php
function enqueue_persona_assets() {
    global $post;

    // Skip if no post content
    if (!is_singular() || empty($post->post_content)) {
        return;
    }

    // Only load assets if shortcodes are present
    if (strpos($post->post_content, '[if_persona') !== false ||
        strpos($post->post_content, '[persona_') !== false) {

        wp_enqueue_style('cme-personas');
        wp_enqueue_script('cme-personas-js');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_persona_assets');
```

## Admin Integration

### Admin Preview

Implementing admin preview functionality:

```php
// Add admin notice for preview mode
function persona_admin_preview_notice() {
    if (!isset($_GET['persona'])) {
        return;
    }

    $persona = sanitize_key($_GET['persona']);
    $persona_name = get_persona_name($persona);

    echo '<div class="notice notice-info">';
    echo '<p>' . sprintf(__('Previewing content as "%s" persona.', 'cme-personas'), esc_html($persona_name)) . '</p>';
    echo '</div>';
}
add_action('admin_notices', 'persona_admin_preview_notice');
```

### TinyMCE Integration

Adding shortcode buttons to the editor:

```php
// Add shortcode button to TinyMCE
function add_persona_shortcode_button($buttons) {
    array_push($buttons, 'persona_shortcodes');
    return $buttons;
}
add_filter('mce_buttons', 'add_persona_shortcode_button');

// Initialize TinyMCE plugin
function add_persona_tinymce_plugin($plugin_array) {
    $plugin_array['persona_shortcodes'] = plugin_dir_url(__FILE__) . 'js/tinymce-persona-plugin.js';
    return $plugin_array;
}
add_filter('mce_external_plugins', 'add_persona_tinymce_plugin');
```

## Edge Cases and Error Handling

### Invalid Persona Handling

```php
/**
 * Check if a persona is valid.
 *
 * @param     string $persona_id    The persona identifier to check.
 * @return    bool                  Whether the persona is valid.
 */
function is_valid_persona($persona_id) {
    // Default is always valid
    if ('default' === $persona_id) {
        return true;
    }

    $persona = get_post($persona_id);
    return ($persona && 'persona' === $persona->post_type && 'publish' === $persona->post_status);
}
```

### Shortcode Nesting Protection

```php
// Track nesting level to prevent infinite recursion
private $nesting_level = 0;

/**
 * Process nested shortcodes safely.
 *
 * @param     string $content   Content to process.
 * @return    string            Processed content.
 */
private function process_nested_shortcodes($content) {
    // Prevent excessive nesting
    if ($this->nesting_level > 10) {
        return $content; // Too deep, just return unprocessed
    }

    $this->nesting_level++;
    $processed = do_shortcode($content);
    $this->nesting_level--;

    return $processed;
}
```
