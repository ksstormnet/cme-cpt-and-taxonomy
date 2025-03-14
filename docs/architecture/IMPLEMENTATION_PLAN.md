# Personas Plugin Implementation Plan

This document outlines the implementation plan for the CME Personas plugin with boundary-based shortcode content personalization capabilities.

## Table of Contents

- [Personas Plugin Implementation Plan](#personas-plugin-implementation-plan)
	- [Table of Contents](#table-of-contents)
	- [Overview](#overview)
	- [Implementation Phases](#implementation-phases)
		- [Phase 1: Core Infrastructure](#phase-1-core-infrastructure)
			- [Timeline: 1-2 Weeks](#timeline-1-2-weeks)
		- [Phase 2: Shortcode System](#phase-2-shortcode-system)
			- [Timeline: 1-2 Weeks](#timeline-1-2-weeks-1)
		- [Phase 3: Admin Integration](#phase-3-admin-integration)
			- [Timeline: 1 Week](#timeline-1-week)
		- [Phase 4: Performance Optimization](#phase-4-performance-optimization)
			- [Timeline: 1 Week](#timeline-1-week-1)
	- [Key Technical Decisions](#key-technical-decisions)
	- [Testing Strategy](#testing-strategy)
	- [Deployment Process](#deployment-process)

## Overview

The goal is to implement the CME Personas plugin using a boundary-based shortcode approach. This architecture focuses on:

1. Using shortcodes to define conditional content boundaries
2. Supporting third-party plugin compatibility
3. Implementing persona detection systems for visitors
4. Creating admin tools for previewing content per persona

The shortcode-based approach provides superior compatibility with existing WordPress plugins and content structures, including complex elements like Meta Slider.

## Implementation Phases

### Phase 1: Core Infrastructure

#### Timeline: 1-2 Weeks

**Deliverables:**

- Persona management system (Custom Post Type)
- Persona detection system (cookie, URL parameter, session-based)
- Core API for persona identification and switching
- Database schema for persona storage

**Tasks:**

1. Create the `Persona_Manager` class for persona detection
2. Implement `Custom_Post_Types` class for persona registration
3. Add helper functions for persona identification
4. Create schema for persona object storage

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
  }

  // Detect current persona from various sources
  public function detect_persona() {
    // Check for URL parameter (highest priority)
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

  // Set the active persona
  public function set_persona($persona_id, $set_cookie = true) {
    if (!$this->is_valid_persona($persona_id)) {
      return false;
    }
    
    $this->current_persona = $persona_id;
    
    if ($set_cookie) {
      setcookie(
        'cme_persona',
        $persona_id,
        time() + WEEK_IN_SECONDS,
        COOKIEPATH,
        COOKIE_DOMAIN
      );
    }
    
    return true;
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

### Phase 2: Shortcode System

#### Timeline: 1-2 Weeks

**Deliverables:**

- `[if_persona]` shortcode for conditional content
- Shortcode processor that preserves third-party compatibility
- Additional utility shortcodes for persona interaction
- Documentation for shortcode usage

**Tasks:**

1. Implement `Frontend` class with shortcode handlers
2. Create `if_persona` shortcode for boundary-based content
3. Add `persona_switcher` shortcode for user persona selection
4. Ensure compatibility with third-party shortcodes
5. Add comprehensive shortcode documentation

**Example Implementation:**

```php
class Frontend {
  public function register() {
    // Register shortcodes
    add_shortcode('if_persona', array($this, 'if_persona_shortcode'));
    add_shortcode('persona_switcher', array($this, 'persona_switcher_shortcode'));
    
    // Add AJAX handler for persona switching
    add_action('wp_ajax_switch_persona', array($this, 'ajax_switch_persona'));
    add_action('wp_ajax_nopriv_switch_persona', array($this, 'ajax_switch_persona'));
  }
  
  /**
   * Shortcode for conditional persona content.
   *
   * Usage: [if_persona is="business"]Business-specific content[/if_persona]
   *        [if_persona is="family,luxury"]Content for family and luxury personas[/if_persona]
   *        [if_persona not="business"]Content for non-business personas[/if_persona]
   */
  public function if_persona_shortcode($atts, $content = null) {
    $atts = shortcode_atts(
      array(
        'is'  => null,
        'not' => null,
      ),
      $atts,
      'if_persona'
    );
    
    // Get current persona
    $persona_manager = Persona_Manager::get_instance();
    $current_persona = $persona_manager->get_current_persona();
    
    // If 'is' attribute is set, check if current persona matches
    if (null !== $atts['is']) {
      $allowed_personas = array_map('trim', explode(',', $atts['is']));
      if (!in_array($current_persona, $allowed_personas, true)) {
        return '';
      }
    }
    
    // If 'not' attribute is set, check if current persona does not match
    if (null !== $atts['not']) {
      $excluded_personas = array_map('trim', explode(',', $atts['not']));
      if (in_array($current_persona, $excluded_personas, true)) {
        return '';
      }
    }
    
    // If we get here, the conditions are met
    return do_shortcode($content);
  }
  
  /**
   * Shortcode for persona switcher.
   *
   * Usage: [persona_switcher]
   *        [persona_switcher display="dropdown" button_text="Switch Persona"]
   */
  public function persona_switcher_shortcode($atts) {
    $atts = shortcode_atts(
      array(
        'display' => 'buttons',
        'button_text' => __('Select Persona', 'cme-personas'),
        'class' => '',
      ),
      $atts,
      'persona_switcher'
    );
    
    // Build the switcher HTML
    // ... Implementation details ...
    
    return $output;
  }
}
```

### Phase 3: Admin Integration

#### Timeline: 1 Week

**Deliverables:**

- Admin sidebar persona selector for previewing content
- Preview URL generation for testing persona content
- TinyMCE integration for easier shortcode insertion
- Admin notices for active persona preview

**Tasks:**

1. Create admin sidebar component for persona selection
2. Implement admin preview URL generation
3. Add TinyMCE shortcode buttons
4. Create admin notification system
5. Add inline documentation and help text

**Example Implementation:**

```php
class Admin {
  public function register() {
    // Add sidebar panel for persona preview
    add_action('add_meta_boxes', array($this, 'add_preview_meta_box'));
    
    // Add admin scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    
    // TinyMCE integration
    add_action('admin_init', array($this, 'tinymce_integration'));
    
    // Admin preview notice
    add_action('admin_notices', array($this, 'preview_notice'));
  }
  
  // Add meta box for persona preview
  public function add_preview_meta_box() {
    $post_types = apply_filters('cme_persona_content_post_types', array('post', 'page'));
    
    foreach ($post_types as $post_type) {
      add_meta_box(
        'persona_preview_meta_box',
        __('Persona Preview', 'cme-personas'),
        array($this, 'render_preview_meta_box'),
        $post_type,
        'side',
        'high'
      );
    }
  }
  
  // Render the persona preview meta box
  public function render_preview_meta_box($post) {
    // Get all personas
    $persona_manager = Persona_Manager::get_instance();
    $personas = $persona_manager->get_all_personas();
    
    // Get preview URL
    $preview_url = get_preview_post_link($post);
    
    echo '<p>' . esc_html__('Preview this page as:', 'cme-personas') . '</p>';
    echo '<ul class="persona-preview-links">';
    
    foreach ($personas as $id => $name) {
      $url = add_query_arg('persona', $id, $preview_url);
      echo '<li><a href="' . esc_url($url) . '" target="_blank">' . esc_html($name) . '</a></li>';
    }
    
    echo '</ul>';
    echo '<p class="description">' . esc_html__('Links open in a new window with the selected persona active.', 'cme-personas') . '</p>';
  }
}
```

### Phase 4: Performance Optimization

#### Timeline: 1 Week

**Deliverables:**

- Optimized shortcode processing
- Persona content caching
- Performance monitoring tools
- Compatibility layer for popular plugins

**Tasks:**

1. Add shortcode detection optimization
2. Implement content caching for personalized content
3. Add compatibility fixes for common third-party plugins
4. Create admin tools for performance monitoring

**Example Implementation:**

```php
class Performance {
  public function register() {
    // Only process shortcodes when necessary
    add_filter('the_content', array($this, 'maybe_process_shortcodes'), 7);
    
    // Add compatibility filters for popular plugins
    add_action('plugins_loaded', array($this, 'plugin_compatibility'));
  }
  
  // Check if content contains persona shortcodes before processing
  public function maybe_process_shortcodes($content) {
    // Skip processing if no persona shortcodes are found
    if (strpos($content, '[if_persona') === false && 
        strpos($content, '[persona_') === false) {
      return $content;
    }
    
    // Process shortcodes
    return do_shortcode($content);
  }
  
  // Add compatibility for popular plugins
  public function plugin_compatibility() {
    // Compatibility with page builders
    if (defined('ELEMENTOR_VERSION')) {
      add_action('elementor/frontend/after_render', array($this, 'elementor_compatibility'));
    }
    
    // Compatibility with caching plugins
    if (defined('WP_CACHE') && WP_CACHE) {
      add_action('wp_footer', array($this, 'cache_compatibility'));
    }
  }
}
```

## Key Technical Decisions

1. **Boundary-Based Shortcode Approach:**

    - Use shortcodes to define conditional content boundaries
    - Preserve content structure and formatting within boundaries
    - Maintain compatibility with third-party shortcodes

2. **Persona Detection:**

    - Use cookies as primary method (with appropriate consent)
    - Support URL parameters for testing and deep linking
    - Prioritize URL parameters over cookies for previewing

3. **Content Processing:**

    - Process shortcodes only when present in content
    - Properly handle nested shortcodes with recursion limits
    - Preserve third-party shortcode execution order

4. **Admin Integration:**
    - Sidebar tools rather than post editing interface
    - Query parameter based preview system
    - Simple URL generation for content testing

## Testing Strategy

1. **Unit Tests:**

    - Persona detection logic
    - Shortcode parsing and processing
    - Parameter handling
    - Nesting support

2. **Integration Tests:**

    - Third-party shortcode compatibility (Meta Slider, etc.)
    - WordPress hooks and filters
    - Admin preview functionality
    - Frontend content display

3. **User Acceptance Testing:**
    - Content author workflow
    - Preview functionality
    - Persona switching
    - Cross-browser compatibility

## Deployment Process

1. **Pre-Deployment:**

    - Version compatibility check
    - Update shortcode documentation
    - Prepare admin guidelines

2. **Deployment:**

    - Core plugin update
    - Verify third-party compatibility
    - Update settings if needed

3. **Post-Deployment:**
    - Author training on shortcode usage
    - Content verification for existing sites
    - Performance monitoring
