# Personas Plugin Implementation Plan

This document outlines the implementation plan for expanding the CME Personas plugin with enhanced content personalization capabilities.

## Table of Contents

- [Personas Plugin Implementation Plan](#personas-plugin-implementation-plan)
    - [Table of Contents](#table-of-contents)
    - [Overview](#overview)
    - [Implementation Phases](#implementation-phases)
        - [Phase 1: Core Infrastructure](#phase-1-core-infrastructure)
            - [Timeline: 1-2 Weeks](#timeline-1-2-weeks)
        - [Phase 2: Admin UI](#phase-2-admin-ui)
            - [Timeline: 1-2 Weeks](#timeline-1-2-weeks-1)
        - [Phase 3: Frontend Integration](#phase-3-frontend-integration)
            - [Timeline: 1-2 Weeks](#timeline-1-2-weeks-2)
        - [Phase 4: Performance Optimization](#phase-4-performance-optimization)
            - [Timeline: 1-2 Weeks](#timeline-1-2-weeks-3)
    - [Key Technical Decisions](#key-technical-decisions)
    - [Testing Strategy](#testing-strategy)
    - [Deployment Process](#deployment-process)

## Overview

The goal is to enhance the existing CME Personas plugin with content personalization features while maintaining the current functionality. The expansion centers on:

1. Adding personalized content management to the existing persona post type
2. Implementing persona detection systems for visitors
3. Creating a frontend API for template integration
4. Adding optional Redis-based caching for performance

The expanded plugin will maintain backward compatibility while providing more sophisticated content personalization capabilities.

## Implementation Phases

### Phase 1: Core Infrastructure

#### Timeline: 1-2 Weeks

**Deliverables:**

- Persona detection system (cookie, URL parameter, session-based)
- Core API for content storage and retrieval
- Persona switching functionality
- Backend data structures for content variations

**Tasks:**

1. Create the `Persona_Manager` class for persona detection
2. Implement `Persona_Content` class for storage and retrieval
3. Add helper functions for template integration
4. Create database schema for content variations

**Example Implementation:**

```php
class Persona_Manager {
  private static $instance = null;
  private $current_persona = null;

  // Singleton implementation
  public static function get_instance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  // Initialize and detect current persona
  public function initialize() {
    add_action('wp', array($this, 'detect_persona'));
    add_filter('template_include', array($this, 'maybe_switch_persona'));
  }

  // Detect current persona from various sources
  public function detect_persona() {
    // Check for URL parameter
    if (isset($_GET['persona']) && $this->is_valid_persona($_GET['persona'])) {
      $this->set_persona(sanitize_text_field($_GET['persona']));
      return;
    }

    // Check for cookie
    if (isset($_COOKIE['cme_persona']) && $this->is_valid_persona($_COOKIE['cme_persona'])) {
      $this->current_persona = sanitize_text_field($_COOKIE['cme_persona']);
      return;
    }

    // Default to 'default' persona
    $this->current_persona = 'default';
  }

  // Check if a persona identifier is valid
  public function is_valid_persona($persona_id) {
    $valid_personas = $this->get_all_personas();
    return isset($valid_personas[$persona_id]);
  }

  // Get all available personas
  public function get_all_personas() {
    $personas = array('default' => 'Default');

    $persona_posts = get_posts(array(
      'post_type' => 'persona',
      'post_status' => 'publish',
      'numberposts' => -1,
    ));

    foreach ($persona_posts as $persona) {
      $key = sanitize_title($persona->post_title);
      $personas[$key] = $persona->post_title;
    }

    return $personas;
  }
}
```

### Phase 2: Admin UI

#### Timeline: 1-2 Weeks

**Deliverables:**

- Tabbed interface for persona content editing
- Block editor integration for content variations
- Preview functionality for persona content
- Settings page enhancements

**Tasks:**

1. Create tabbed UI for content editing
2. Implement meta boxes for persona-specific fields
3. Add preview functionality
4. Enhance settings page with persona options
5. Add inline documentation and help text

**Example Implementation:**

```php
class Persona_Admin {
  public function register() {
    add_action('add_meta_boxes', array($this, 'add_persona_content_meta_box'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('save_post', array($this, 'save_persona_content'));
  }

  // Add meta box for persona-specific content
  public function add_persona_content_meta_box() {
    $post_types = apply_filters('cme_persona_content_post_types', array('post', 'page'));

    foreach ($post_types as $post_type) {
      add_meta_box(
        'persona_content_meta_box',
        __('Persona-Specific Content', 'cme-personas'),
        array($this, 'render_persona_content_meta_box'),
        $post_type,
        'normal',
        'high'
      );
    }
  }

  // Render the persona content editing interface
  public function render_persona_content_meta_box($post) {
    // Create nonce for verification
    wp_nonce_field('persona_content_nonce', 'persona_content_nonce');

    // Get all available personas
    $persona_manager = Persona_Manager::get_instance();
    $personas = $persona_manager->get_all_personas();

    // Get existing values
    $persona_content = get_post_meta($post->ID, '_persona_content', true);
    if (!is_array($persona_content)) {
      $persona_content = array();
    }

    // Output tabs and content areas
    echo '<div class="persona-content-tabs">';
    echo '<ul class="persona-tabs-nav">';

    foreach ($personas as $key => $label) {
      $active = ($key === 'default') ? 'active' : '';
      echo '<li><a href="#persona-tab-' . esc_attr($key) . '" class="' . esc_attr($active) . '">' . esc_html($label) . '</a></li>';
    }

    echo '</ul>';
    echo '<div class="persona-tabs-content">';

    foreach ($personas as $key => $label) {
      $content = isset($persona_content[$key]) ? $persona_content[$key] : '';
      $display = ($key === 'default') ? 'block' : 'none';

      echo '<div id="persona-tab-' . esc_attr($key) . '" class="persona-tab-panel" style="display: ' . esc_attr($display) . ';">';
      echo '<h3>' . esc_html(sprintf(__('Content for %s Persona', 'cme-personas'), $label)) . '</h3>';

      wp_editor(
        $content,
        'persona_content_' . sanitize_key($key),
        array(
          'textarea_name' => 'persona_content[' . esc_attr($key) . ']',
          'media_buttons' => true,
          'textarea_rows' => 10,
        )
      );

      echo '</div>';
    }

    echo '</div>'; // .persona-tabs-content
    echo '</div>'; // .persona-content-tabs
  }
}
```

### Phase 3: Frontend Integration

#### Timeline: 1-2 Weeks

**Deliverables:**

- Public API for template developers
- Shortcodes for persona-specific content
- Helper functions for theme integration
- Frontend persona switching capability

**Tasks:**

1. Create template functions for retrieving content
2. Implement shortcodes for personalized content
3. Add frontend persona switcher
4. Create documentation and examples

**Example Implementation:**

```php
// Public API for themes and plugins
function cme_get_persona_content($post_id, $field = 'content', $persona = null) {
  // Get persona manager
  $persona_manager = Persona_Manager::get_instance();

  // Use current persona if none specified
  if (null === $persona) {
    $persona = $persona_manager->get_current_persona();
  }

  // Get content manager
  $content_manager = Persona_Content::get_instance();

  // Get persona-specific content
  return $content_manager->get_content($post_id, $field, $persona);
}

// Register shortcodes
function cme_register_persona_shortcodes() {
  add_shortcode('persona_content', 'cme_persona_content_shortcode');
  add_shortcode('persona_switcher', 'cme_persona_switcher_shortcode');
}
add_action('init', 'cme_register_persona_shortcodes');

// Shortcode for persona-specific content
function cme_persona_content_shortcode($atts, $content = null) {
  $atts = shortcode_atts(
    array(
      'persona' => '',
      'default' => '',
    ),
    $atts,
    'persona_content'
  );

  // Get current persona
  $persona_manager = Persona_Manager::get_instance();
  $current_persona = $persona_manager->get_current_persona();

  // If this content is for the current persona or all personas, show it
  if (empty($atts['persona']) || $atts['persona'] === $current_persona) {
    return do_shortcode($content);
  }

  // If default content provided, return that
  if (!empty($atts['default'])) {
    return $atts['default'];
  }

  return '';
}
```

### Phase 4: Performance Optimization

#### Timeline: 1-2 Weeks

**Deliverables:**

- Optional Redis-based caching for persona content
- Performance monitoring tools
- Preloading capability for high-traffic sites
- Cache invalidation system

**Tasks:**

1. Create Redis connection manager (optional dependency)
2. Implement caching layer for persona content
3. Add cache invalidation on content updates
4. Create admin tools for cache management
5. Performance testing and optimization

**Example Implementation:**

```php
class Persona_Cache {
  private static $instance = null;
  private $redis = null;
  private $enabled = false;

  // Singleton implementation
  public static function get_instance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  // Initialize caching
  public function initialize() {
    // Check if Redis caching is enabled
    $this->enabled = get_option('cme_personas_enable_redis', false);

    if ($this->enabled) {
      $this->connect_to_redis();
    }

    // Register cache invalidation hooks
    add_action('save_post', array($this, 'invalidate_content_cache'));
    add_action('deleted_post', array($this, 'invalidate_content_cache'));
  }

  // Try to connect to Redis
  private function connect_to_redis() {
    if (!class_exists('Redis')) {
      $this->enabled = false;
      return false;
    }

    try {
      $this->redis = new Redis();
      $host = defined('WP_REDIS_HOST') ? WP_REDIS_HOST : '127.0.0.1';
      $port = defined('WP_REDIS_PORT') ? WP_REDIS_PORT : 6379;

      if ($this->redis->connect($host, $port)) {
        if (defined('WP_REDIS_PASSWORD') && WP_REDIS_PASSWORD) {
          $this->redis->auth(WP_REDIS_PASSWORD);
        }
        return true;
      }
    } catch (Exception $e) {
      $this->enabled = false;
      error_log('Redis connection error: ' . $e->getMessage());
    }

    return false;
  }

  // Get cached content
  public function get($key) {
    if (!$this->enabled || !$this->redis) {
      return false;
    }

    $prefixed_key = 'cme_persona:' . $key;
    return $this->redis->get($prefixed_key);
  }

  // Set cached content
  public function set($key, $value, $expiry = 86400) {
    if (!$this->enabled || !$this->redis) {
      return false;
    }

    $prefixed_key = 'cme_persona:' . $key;
    return $this->redis->setEx($prefixed_key, $expiry, $value);
  }

  // Invalidate content cache
  public function invalidate_content_cache($post_id) {
    if (!$this->enabled || !$this->redis) {
      return false;
    }

    // Get all personas
    $persona_manager = Persona_Manager::get_instance();
    $personas = $persona_manager->get_all_personas();

    // Delete cache for each persona
    foreach (array_keys($personas) as $persona) {
      $key = 'cme_persona:post:' . $post_id . ':' . $persona;
      $this->redis->del($key);
    }

    return true;
  }
}
```

## Key Technical Decisions

1. **Content Storage Method:**

    - Store persona content as post meta for compatibility with existing systems
    - Use structured meta format for different content fields
    - Include block identifier support for partial content replacement

2. **Persona Detection:**

    - Use cookies as primary method (with appropriate consent)
    - Support URL parameters for testing and deep linking
    - Include JavaScript API for dynamic switching

3. **Redis Integration:**

    - Make Redis optional with graceful fallback
    - Use WordPress caching API when available
    - Implement tiered cache approach (object cache → Redis → database)

4. **Admin UI:**
    - Use tabs for persona content editing
    - Support standard WordPress block editor
    - Provide inline documentation and contextual help

## Testing Strategy

1. **Unit Tests:**

    - Persona detection logic
    - Content storage and retrieval
    - Shortcode processing
    - Redis connection and fallback

2. **Integration Tests:**

    - WordPress hooks and filters
    - Admin UI functionality
    - Frontend content display
    - Performance under load

3. **User Acceptance Testing:**
    - Content editor workflow
    - Preview functionality
    - Persona switching
    - Cache management

## Deployment Process

1. **Pre-Deployment:**

    - Version compatibility check
    - Database schema update preparation
    - Documentation update

2. **Deployment:**

    - Database schema updates
    - New functionality rollout
    - Settings migration

3. **Post-Deployment:**
    - Cache warming
    - Performance monitoring
    - User training
