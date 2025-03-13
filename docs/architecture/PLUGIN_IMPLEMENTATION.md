# Personas Plugin Implementation Plan

This document outlines the complete implementation plan for creating a dedicated WordPress Personas plugin that provides persona-based content functionality. This plugin will be separate from but integrated with the Cruise Made Easy plugin.

## Table of Contents

- [Personas Plugin Implementation Plan](#personas-plugin-implementation-plan)
    - [Table of Contents](#table-of-contents)
    - [Overview](#overview)
    - [Plugin Architecture](#plugin-architecture)
    - [Phase 1: Core Infrastructure](#phase-1-core-infrastructure)
        - [Tasks](#tasks)
        - [Deliverables](#deliverables)
        - [Core Classes](#core-classes)
            - [Persona Manager Class](#persona-manager-class)
            - [Persona Content Class](#persona-content-class)
    - [Phase 2: Admin UI](#phase-2-admin-ui)
        - [Tasks](#tasks-1)
        - [Deliverables](#deliverables-1)
        - [Admin UI Implementation](#admin-ui-implementation)
            - [Admin Class](#admin-class)

## Overview

The Personas plugin will provide a comprehensive system for creating and managing persona-specific content across any WordPress site. It will use structured object storage with post meta, and feature a user-friendly editing interface integrated with the Gutenberg block editor.

Key features:

- Persona detection from GTM/cookies
- Content storage and retrieval system
- Admin UI for managing persona content
- Frontend content replacement
- Optional Redis caching for performance

## Plugin Architecture

The plugin will follow WordPress best practices with a modular architecture:

```php
wp-content/plugins/personas/
├── personas.php                    # Main plugin file
├── includes/                       # Core functionality
│   ├── class-personas.php          # Main plugin class
│   ├── class-activator.php         # Activation hooks
│   ├── class-deactivator.php       # Deactivation hooks
│   ├── class-persona-manager.php   # Persona management
│   ├── class-persona-content.php   # Content storage/retrieval
│   └── class-persona-cache.php     # Optional Redis caching
├── admin/                          # Admin functionality
│   ├── class-personas-admin.php    # Admin functionality
│   ├── js/
│   │   └── personas-admin.js       # Admin JavaScript
│   ├── css/
│   │   └── personas-admin.css      # Admin styles
│   └── partials/                   # Admin view templates
├── public/                         # Frontend functionality
│   ├── class-personas-public.php   # Public functionality
│   ├── js/
│   │   └── personas-public.js      # Public JavaScript
│   └── css/
│       └── personas-public.css     # Public styles
└── languages/                      # Internationalization
```

## Phase 1: Core Infrastructure

### Tasks

1. **Create Plugin Scaffold**

    - Set up plugin directory structure
    - Create main plugin file with metadata
    - Implement activation/deactivation hooks

2. **Create Persona Management Class**

    - Define available personas
    - Implement persona detection from GTM/cookies
    - Add functions to get/set current persona

3. **Implement Storage System**
    - Create functions to save/retrieve persona variations
    - Define schema for structured content storage
    - Add metadata registration for persona content

### Deliverables

- Complete plugin scaffold
- Core classes for persona management
- Content storage and retrieval system

### Core Classes

#### Persona Manager Class

```php
/**
 * Class Personas_Manager
 *
 * Handles persona detection, management, and context.
 */
class Personas_Manager {
    /**
     * Singleton instance.
     *
     * @var Personas_Manager
     */
    private static $instance = null;

    /**
     * Available personas.
     *
     * @var array
     */
    private $personas = [];

    /**
     * Current persona.
     *
     * @var string
     */
    private $current_persona = 'default';

    /**
     * Get singleton instance.
     *
     * @return Personas_Manager
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Load available personas
        $this->load_personas();

        // Detect current persona
        add_action('init', array($this, 'detect_persona'), 5);
    }

    /**
     * Load available personas.
     */
    private function load_personas() {
        // Default personas
        $this->personas = [
            'default' => __('Default', 'personas'),
            'easy-breezy' => __('Easy-Breezy Cruiser', 'personas'),
            'luxe' => __('Luxe Seafarer', 'personas'),
            'thrill-seeker' => __('Thrill Seeker', 'personas')
        ];

        // Allow modification via filter
        $this->personas = apply_filters('personas_available_personas', $this->personas);
    }

    /**
     * Get available personas.
     *
     * @return array
     */
    public function get_available_personas() {
        return $this->personas;
    }

    /**
     * Detect current persona from GTM/cookies/URL.
     */
    public function detect_persona() {
        $persona = 'default';

        // Try cookie first
        if (isset($_COOKIE['_gtm_persona'])) {
            $cookie_persona = sanitize_key($_COOKIE['_gtm_persona']);
            if ($this->is_valid_persona($cookie_persona)) {
                $persona = $cookie_persona;
            }
        }

        // Try URL parameter
        if (isset($_GET['persona'])) {
            $url_persona = sanitize_key($_GET['persona']);
            if ($this->is_valid_persona($url_persona)) {
                $persona = $url_persona;

                // Set cookie for future requests
                if (!headers_sent()) {
                    setcookie('_gtm_persona', $persona, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                }
            }
        }

        // Allow custom detection via filter
        $persona = apply_filters('personas_detected_persona', $persona);

        // Set current persona
        $this->current_persona = $persona;

        // Maybe set up data layer for GTM
        if (!is_admin()) {
            add_action('wp_head', array($this, 'maybe_setup_gtm_datalayer'), 1);
        }
    }

    /**
     * Check if a persona is valid.
     *
     * @param string $persona Persona to check.
     * @return bool
     */
    public function is_valid_persona($persona) {
        return array_key_exists($persona, $this->personas);
    }

    /**
     * Get current persona.
     *
     * @return string
     */
    public function get_current_persona() {
        return $this->current_persona;
    }

    /**
     * Set current persona.
     *
     * @param string $persona Persona to set.
     * @return bool Success.
     */
    public function set_current_persona($persona) {
        if ($this->is_valid_persona($persona)) {
            $this->current_persona = $persona;
            return true;
        }
        return false;
    }

    /**
     * Maybe set up GTM data layer.
     */
    public function maybe_setup_gtm_datalayer() {
        // Only if GTM integration is enabled
        if (!get_option('personas_gtm_integration', true)) {
            return;
        }

        // Output data layer
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'persona': '<?php echo esc_js($this->current_persona); ?>'
        });
        </script>
        <?php
    }
}
```

#### Persona Content Class

```php
/**
 * Class Personas_Content
 *
 * Handles storage and retrieval of persona-specific content.
 */
class Personas_Content {
    /**
     * Singleton instance.
     *
     * @var Personas_Content
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Personas_Content
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Set up content filters
        add_action('init', array($this, 'setup_content_filters'));
    }

    /**
     * Set up content filters.
     */
    public function setup_content_filters() {
        // Skip in admin
        if (is_admin()) {
            return;
        }

        // Main content filter
        add_filter('the_content', array($this, 'filter_content'), 10, 1);

        // Title filter
        add_filter('the_title', array($this, 'filter_title'), 10, 2);

        // Featured image filter
        add_filter('post_thumbnail_id', array($this, 'filter_featured_image'), 10, 2);
    }

    /**
     * Save persona variations for a post.
     *
     * @param int $post_id The post ID.
     * @param string $persona The persona identifier.
     * @param array $variations Content variations.
     * @return bool Success.
     */
    public function save_variations($post_id, $persona, $variations) {
        // Validate inputs
        if (empty($post_id) || empty($persona)) {
            return false;
        }

        // Ensure we have array of variations
        if (!is_array($variations)) {
            return false;
        }

        // Generate meta key
        $meta_key = "_personas_{$persona}_variations";

        // Save variations
        return update_post_meta($post_id, $meta_key, $variations);
    }

    /**
     * Get persona variations for a post.
     *
     * @param int $post_id The post ID.
     * @param string $persona The persona identifier.
     * @return array Content variations.
     */
    public function get_variations($post_id, $persona) {
        // Generate meta key
        $meta_key = "_personas_{$persona}_variations";

        // Get variations
        $variations = get_post_meta($post_id, $meta_key, true);

        // Ensure we return an array
        return is_array($variations) ? $variations : [];
    }

    /**
     * Filter content for current persona.
     *
     * @param string $content The content.
     * @return string Filtered content.
     */
    public function filter_content($content) {
        // Get current post ID
        $post_id = get_the_ID();
        if (!$post_id) {
            return $content;
        }

        // Get current persona
        $persona_manager = Personas_Manager::get_instance();
        $persona = $persona_manager->get_current_persona();

        // Skip for default persona
        if ($persona === 'default') {
            return $content;
        }

        // Get variations
        $variations = $this->get_variations($post_id, $persona);
        if (empty($variations)) {
            return $content;
        }

        // Parse blocks
        $blocks = parse_blocks($content);

        // Apply variations
        $blocks = $this->apply_variations_to_blocks($blocks, $variations);

        // Return modified content
        return serialize_blocks($blocks);
    }

    /**
     * Apply variations to parsed blocks.
     *
     * @param array $blocks Parsed blocks.
     * @param array $variations Content variations.
     * @return array Modified blocks.
     */
    private function apply_variations_to_blocks($blocks, $variations) {
        foreach ($blocks as &$block) {
            // Check if we have a block ID
            if (!empty($block['attrs']['id']) && isset($variations[$block['attrs']['id']])) {
                $block_id = $block['attrs']['id'];
                $variation = $variations[$block_id];

                // Apply variation based on block type
                $this->apply_variation_to_block($block, $variation);
            }

            // Process inner blocks recursively
            if (!empty($block['innerBlocks'])) {
                $block['innerBlocks'] = $this->apply_variations_to_blocks(
                    $block['innerBlocks'],
                    $variations
                );
            }
        }

        return $blocks;
    }

    /**
     * Apply variation to a specific block.
     *
     * @param array &$block Block to modify.
     * @param mixed $variation Variation data.
     */
    private function apply_variation_to_block(&$block, $variation) {
        switch ($block['blockName']) {
            case 'core/paragraph':
                // For paragraphs, update content
                $block['attrs']['content'] = $variation;
                if (isset($block['innerHTML'])) {
                    $block['innerHTML'] = wpautop($variation);
                }
                break;

            case 'core/heading':
                // For headings, update content
                $block['attrs']['content'] = $variation;
                if (isset($block['innerHTML'])) {
                    $level = $block['attrs']['level'] ?? 2;
                    $block['innerHTML'] = "<h{$level}>{$variation}</h{$level}>";
                }
                break;

            case 'core/image':
                // For images, update attachment ID
                if (is_numeric($variation)) {
                    $block['attrs']['id'] = intval($variation);

                    // Also update other image attributes
                    $image = wp_get_attachment_image_src(intval($variation), 'full');
                    if ($image) {
                        $block['attrs']['url'] = $image[0];
                    }
                }
                break;

            default:
                // For unknown blocks, try to update content attribute
                if (is_string($variation) && isset($block['attrs']['content'])) {
                    $block['attrs']['content'] = $variation;
                }
                elseif (is_array($variation)) {
                    // For array variations, update multiple attributes
                    foreach ($variation as $key => $value) {
                        $block['attrs'][$key] = $value;
                    }
                }
                break;
        }
    }

    /**
     * Filter title for current persona.
     *
     * @param string $title The title.
     * @param int $post_id The post ID.
     * @return string Filtered title.
     */
    public function filter_title($title, $post_id) {
        // Skip empty post ID
        if (!$post_id) {
            return $title;
        }

        // Get current persona
        $persona_manager = Personas_Manager::get_instance();
        $persona = $persona_manager->get_current_persona();

        // Skip for default persona
        if ($persona === 'default') {
            return $title;
        }

        // Get variations
        $variations = $this->get_variations($post_id, $persona);

        // Check for title variation
        if (!empty($variations['title'])) {
            return $variations['title'];
        }

        return $title;
    }

    /**
     * Filter featured image for current persona.
     *
     * @param int $thumbnail_id The thumbnail ID.
     * @param int|WP_Post $post The post object or ID.
     * @return int Filtered thumbnail ID.
     */
    public function filter_featured_image($thumbnail_id, $post) {
        // Get post ID
        $post_id = is_object($post) ? $post->ID : (int)$post;

        // Skip empty post ID
        if (!$post_id) {
            return $thumbnail_id;
        }

        // Get current persona
        $persona_manager = Personas_Manager::get_instance();
        $persona = $persona_manager->get_current_persona();

        // Skip for default persona
        if ($persona === 'default') {
            return $thumbnail_id;
        }

        // Get variations
        $variations = $this->get_variations($post_id, $persona);

        // Check for featured image variation
        if (!empty($variations['featured_image'])) {
            return (int)$variations['featured_image'];
        }

        return $thumbnail_id;
    }
}
```

## Phase 2: Admin UI

### Tasks

1. **Create Admin Settings Page**

    - Implement plugin configuration
    - Add persona management
    - Create GTM integration settings

2. **Implement Gutenberg Integration**

    - Create meta box for post editor
    - Implement tabbed interface for personas
    - Add block content editors

3. **Add Media Management**
    - Implement featured image selection
    - Add media selection for blocks

### Deliverables

- Admin settings page
- Post editor integration
- JavaScript for Gutenberg integration

### Admin UI Implementation

#### Admin Class

```php
/**
 * Class Personas_Admin
 *
 * Handles admin-related functionality.
 */
class Personas_Admin {
    /**
     * Initialize the class.
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        // Save post meta
        add_action('save_post', array($this, 'save_post_meta'));

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __('Personas Settings', 'personas'),
            __('Personas', 'personas'),
            'manage_options',
            'personas-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        // Register settings group
        register_setting('personas_settings', 'personas_settings');

        // Add settings sections
        add_settings_section(
            'personas_general_section',
            __('General Settings', 'personas'),
            array($this, 'render_general_section'),
            'personas_settings'
        );

        add_settings_section(
            'personas_gtm_section',
            __('Google Tag Manager Integration', 'personas'),
            array($this, 'render_gtm_section'),
            'personas_settings'
        );

        // Add settings fields
        add_settings_field(
            'personas_post_types',
            __('Enabled Post Types', 'personas'),
            array($this, 'render_post_types_field'),
            'personas_settings',
            'personas_general_section'
        );

        add_settings_field(
            'personas_available_personas',
            __('Available Personas', 'personas'),
            array($this, 'render_personas_field'),
            'personas_settings',
            'personas_general_section'
        );

        add_settings_field(
            'personas_gtm_integration',
            __('GTM Integration', 'personas'),
            array($this, 'render_gtm_integration_field'),
            'personas_settings',
            'personas_gtm_section'
        );
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('personas_settings');
                do_settings_sections('personas_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general section.
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general settings for the Personas plugin.', 'personas') . '</p>';
    }

    /**
     * Render GTM section.
     */
    public function render_gtm_section() {
        echo '<p>' . __('Configure Google Tag Manager integration.', 'personas') . '</p>';
    }

    /**
     * Render post types field.
     */
    public function render_post_types_field() {
        $options = get_option('personas_settings', array());
        $enabled_post_types = isset($options['post_types']) ? $options['post_types'] : array('post', 'page');

        // Get all public post types
        $post_types = get_post_types(array('public' => true), 'objects');

        foreach ($post_types as $post_type) {
            $id = $post_type->name;
            $checked = in_array($id, $enabled_post_types) ? 'checked' : '';

            echo '<label>';
            echo '<input type="checkbox" name="personas_settings[post_types][]" value="' . esc_attr($id) . '" ' . $checked . '>';
            echo esc_html($post_type->label);
            echo '</label><br>';
        }

        echo '<p class="description">' . __('Select which post types should have persona support.', 'personas') . '</p>';
    }

    /**
     * Render personas field.
     */
    public function render_personas_field() {
        $options = get_option('personas_settings', array());
        $personas = isset($options['personas']) ? $options['personas'] : array(
            'default' => __('Default', 'personas'),
            'easy-breezy' => __('Easy-Breezy Cruiser', 'personas'),
            'luxe' => __('Luxe Seafarer', 'personas'),
            'thrill-seeker' => __('Thrill Seeker', 'personas')
        );

        // Hidden field for default persona
        echo '<input type="hidden" name="personas_settings[personas][default]" value="' . esc_attr($personas['default']) . '">';

        // Display table for other personas
        echo '<table class="wp-list-table widefat fixed striped" id="personas-table">';
        echo '<thead><tr>';
        echo '<th>' . __('ID', 'personas') . '</th>';
        echo '<th>' . __('Name', 'personas') . '</th>';
        echo '<th>' . __('Actions', 'personas') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($personas as $id => $name) {
            if ($id === 'default') continue; // Skip default

            echo '<tr>';
            echo '<td><input type="text" name="personas_settings[personas_ids][]" value="' . esc_attr($id) . '" readonly></td>';
            echo '<td><input type="text" name="personas_settings[personas_names][]" value="' . esc_attr($name) . '"></td>';
            echo '<td><button type="button" class="button remove-persona">' . __('Remove', 'personas') . '</button></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '<tfoot><tr>';
        echo '<td colspan="3"><button type="button" class="button add-persona">' . __('Add Persona', 'personas') . '</button></td>';
        echo '</tr></tfoot>';
        echo '</table>';

        // JavaScript for table interactions
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Add persona
            $('.add-persona').on('click', function() {
                var row = $('<tr></tr>');
                row.append('<td><input type="text" name="personas_settings[personas_ids][]" value=""></td>');
                row.append('<td><input type="text" name="personas_settings[personas_names][]" value=""></td>');
                row.append('<td><button type="button" class="button remove-persona">Remove</button></td>');

                $('#personas-table tbody').append(row);
            });

            // Remove persona
            $(document).on('click', '.remove-persona', function() {
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Render GTM integration field.
     */
    public function render_gtm_integration_field() {
        $options = get_option('personas_settings', array());
        $gtm_integration = isset($options['gtm_integration']) ? $options['gtm_integration'] : true;

        echo '<label>';
        echo '<input type="checkbox" name="personas_settings[gtm_integration]" value="1" ' . checked(true, $gtm_integration, false) . '>';
        echo __('Enable GTM integration', 'personas');
        echo '</label>';

        echo '<p class="description">' . __('When enabled, persona information will be pushed to the GTM data layer.', 'personas') . '</p>';
    }

    /**
     * Add meta boxes.
     */
    public function add_meta_boxes() {
        // Get enabled post types
        $options = get_option('personas_settings', array());
        $post_types = isset($options['post_types']) ? $options['post_types'] : array('post', 'page');

        // Add meta box to enabled post types
        foreach ($post_types as $post_type) {
            add_meta_box(
                'personas_content',
                __('Persona Content', 'personas'),
                array($this, 'render_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render meta box.
     *
     * @param WP_Post $post Current post.
     */
    public function render_meta_box($post) {
        // Get persona manager
        $persona_manager = Personas_Manager::get_instance();
        $personas = $persona_manager->get_available_personas();

        // Get persona content
        $content_handler = Personas_Content::get_instance();

        // Output nonce field
        wp_nonce_field('personas_save_meta', 'personas_meta_nonce');

        // Output tabs container
        echo '<div class="personas-tabs">';

        // Output tabs navigation
        echo '<div class="personas-tabs-nav">';
        foreach ($personas as $id => $name) {
            if ($id === 'default') continue; // Skip default

            $active = $id === 'easy-breezy' ? ' active' : '';
            echo '<a href="#" data-tab="' . esc_attr($id) . '" class="personas-tab-nav' . $active . '">' . esc_html($name) . '</a>';
        }
        echo '</div>';

        // Output tabs content
        echo '<div class="personas-tabs-content">';

        foreach ($personas as $id => $name) {
            if ($id === 'default') continue; // Skip default

            // Get variations for this persona
            $variations = $content_handler->get_variations($post->ID, $id);

            $active = $id === 'easy-breezy' ? ' active' : '';
            echo '<div id="personas-tab-' . esc_attr($id) . '" class="personas-tab' . $active . '">';

            // Title variation
            echo '<div class="personas-field">';
            echo '<label for="personas-' . esc_attr($id) . '-title">' . __('Title:', 'personas') . '</label>';
            echo '<input type="text" id="personas-' . esc_attr($id) . '-title" name="personas[' . esc_attr($id) . '][title]" value="' . esc_attr($variations['title'] ?? '') . '" class="widefat">';
            echo '</div>';

            // Featured image variation
            echo '<div class="personas-field">';
            echo '<label>' . __('Featured Image:', 'personas') . '</label>';

            $featured_id = $variations['featured_image'] ?? '';
            $featured_url = $featured_id ? wp_get_attachment_image_url($featured_id, 'thumbnail') : '';

            echo '<div class="personas-featured-image">';
            if ($featured_url) {
                echo '<img src="' . esc_url($featured_url) . '" alt="">';
            }
            echo '</div>';

            echo '<input type="hidden" name="personas[' . esc_attr($id) . '][featured_image]" value="' . esc_attr($featured_id) . '" id="personas-' . esc_attr($id) . '-featured-image">';
            echo '<button type="button" class="button personas-select-image" data-target="personas-' . esc_attr($id) . '-featured-image">' . __('Select Image', 'personas') . '</button>';

            if ($featured_id) {
                echo ' <button type="button" class="button personas-remove-image" data-target="personas-' . esc_attr($id) . '-featured-image">' . __('Remove', 'personas') . '</button>';
            }

            echo '</div>';

            // Blocks container
            echo '<div class="personas-blocks" data-persona="' . esc_attr($id) . '">';
            echo '<h3>' . __('Content Blocks', 'personas') . '</h3>';
            echo '<p class="description">' . __('Edit content for specific blocks. Leave empty to use default content.', 'personas') . '</p>';
            echo '</div>';

            echo '</div>'; // End tab
        }

        echo '</div>'; // End tabs content
        echo '</div>'; // End tabs container
    }

    /**
     * Save post meta.
     *
     * @param int $post_id The post ID.
     */
    public function save_post_meta($post_id) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check security nonce
        if (!isset($_POST['personas_meta_nonce']) ||
            !wp_verify_nonce($_POST['personas_meta_nonce'], 'personas_save_meta')) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Process personas data
        if (isset($_POST['personas']) && is_array($_POST['personas'])) {
            $content_handler = Personas_Content::get_instance();

            foreach ($_POST['personas'] as $persona => $data) {
                // Sanitize data
                $variations = array();

                // Title
                if (!empty($data['title'])) {
                    $variations['title'] = sanitize_text_
```
