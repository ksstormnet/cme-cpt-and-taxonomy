# Hybrid Redis-Based Persona Implementation Plan

This document outlines the implementation plan for the hybrid Redis-based persona content system. This approach provides optimal performance, maintainability, and portability across any CDN or hosting setup.

## Table of Contents

- [Hybrid Redis-Based Persona Implementation Plan](#hybrid-redis-based-persona-implementation-plan)
	- [Table of Contents](#table-of-contents)
	- [Overview](#overview)
	- [Implementation Phases](#implementation-phases)
		- [Phase 1: Infrastructure Setup](#phase-1-infrastructure-setup)
		- [Phase 2: Core Caching Layer](#phase-2-core-caching-layer)
		- [Phase 3: Preloading System](#phase-3-preloading-system)
		- [Phase 4: Invalidation System](#phase-4-invalidation-system)
		- [Phase 5: Monitoring and Optimization](#phase-5-monitoring-and-optimization)
	- [Key Technical Decisions](#key-technical-decisions)
	- [Testing Strategy](#testing-strategy)
	- [Deployment Process](#deployment-process)
	- [Maintenance Considerations](#maintenance-considerations)

## Overview

The hybrid approach centers on:

1. Pre-rendering all persona content variations in background processes
2. Storing these variations in Redis with appropriate keys and TTLs
3. Implementing fast, direct lookups on page requests
4. Maintaining a robust invalidation system for content updates
5. Integrating with existing CDN and caching layers for multi-level performance

This delivers the best combination of performance and maintainability without requiring complex edge-worker logic or specialized CDN features.

## Implementation Phases

### Phase 1: Infrastructure Setup

**Timeline: 1 Week**

**Deliverables:**
- Redis infrastructure configured and connected
- Base WordPress plugin structure for Redis integration
- Initial key schema defined and documented
- Configuration system for Redis connection parameters

**Tasks:**
1. Set up Redis server or cluster (production and staging)
2. Create `CME_Cruises_Redis_Manager` class for Redis interaction
3. Implement connection handling with appropriate error recovery
4. Create configuration interface in WordPress admin
5. Develop unit tests for Redis connection and basic operations

**Example Implementation:**
```php
class CME_Cruises_Redis_Manager {
  private static $instance = null;

  // Singleton implementation
  public static function get_instance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  // Initialize Redis connection
  public function initialize() {
    // Set up Redis client using wp-redis or custom connection
    if (function_exists('wp_redis_get_client')) {
      $this->redis = wp_redis_get_client();
      return true;
    }

    // Fallback manual connection
    try {
      $host = defined('CME_REDIS_HOST') ? CME_REDIS_HOST : '127.0.0.1';
      $port = defined('CME_REDIS_PORT') ? CME_REDIS_PORT : 6379;

      $this->redis = new Redis();
      $this->redis->connect($host, $port);

      if (defined('CME_REDIS_AUTH') && CME_REDIS_AUTH) {
        $this->redis->auth(CME_REDIS_AUTH);
      }

      return true;
    } catch (Exception $e) {
      // Log error and continue without Redis
      error_log('Redis connection error: ' . $e->getMessage());
      return false;
    }
  }
}
```

### Phase 2: Core Caching Layer

**Timeline: 2 Weeks**

**Deliverables:**
- Base content retrieval system
- Redis key structure implementation
- Cache storage and retrieval methods
- Fallback content generation
- Integration with existing WordPress functions

**Tasks:**
1. Implement persona detection from cookies/GTM
2. Create the Redis key schema and management system
3. Develop content generation fallback for cache misses
4. Integrate with template loading and content filters
5. Create basic cache statistics tracking

**Example Implementation:**
```php
class CME_Cruises_Persona_Content_Cache {
  private $redis_manager;

  public function __construct() {
    $this->redis_manager = CME_Cruises_Redis_Manager::get_instance();

    // Hook into content filters
    add_filter('the_content', array($this, 'get_persona_content'), 10, 1);
  }

  // Get persona-specific content, with Redis caching
  public function get_persona_content($content) {
    $post_id = get_the_ID();
    $persona = $this->get_current_persona();

    // Skip caching on admin or certain contexts
    if (is_admin() || !$post_id) {
      return $content;
    }

    // Try to get content from Redis cache
    $cache_key = $this->get_cache_key($post_id, $persona);
    $cached_content = $this->get_from_cache($cache_key);

    if (false !== $cached_content) {
      return $cached_content;
    }

    // Cache miss - generate content
    $generated_content = $this->generate_persona_content($post_id, $persona, $content);

    // Store in cache with appropriate TTL
    $this->store_in_cache($cache_key, $generated_content);

    return $generated_content;
  }

  private function get_cache_key($post_id, $persona) {
    return "cme:persona:{$persona}:post:{$post_id}:content";
  }

  private function get_from_cache($key) {
    if (!$this->redis_manager->is_available()) {
      return false;
    }

    return wp_cache_get($key, 'redis');
  }

  private function store_in_cache($key, $content, $ttl = WEEK_IN_SECONDS) {
    if (!$this->redis_manager->is_available()) {
      return false;
    }

    return wp_cache_set($key, $content, 'redis', $ttl);
  }
}
```

### Phase 3: Preloading System

**Timeline: 2 Weeks**

**Deliverables:**
- Background content generation system
- Scheduled regeneration jobs
- Batch processing system for large content sets
- Prioritization system for most-viewed content

**Tasks:**
1. Create WordPress cron tasks for regeneration
2. Implement batch processing for large content libraries
3. Develop progress tracking and logging system
4. Create admin UI for manual cache warming
5. Implement priority system based on page views

**Example Implementation:**
```php
class CME_Cruises_Persona_Preloader {
  private $redis_manager;
  private $content_generator;

  public function __construct() {
    $this->redis_manager = CME_Cruises_Redis_Manager::get_instance();
    $this->content_generator = new CME_Cruises_Persona_Content_Generator();

    // Register cron hooks
    add_action('cme_preload_persona_content', array($this, 'preload_single_post'), 10, 2);
    add_action('cme_scheduled_full_preload', array($this, 'schedule_full_preload'));

    // Register content update hooks
    add_action('save_post', array($this, 'schedule_post_preload'), 10, 3);
  }

  // Schedule preloading of all personas for a single post
  public function schedule_post_preload($post_id, $post, $update) {
    // Skip revisions, auto-saves, etc.
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
      return;
    }

    // Get all active personas
    $personas = $this->get_active_personas();

    // Schedule a job for each persona
    foreach ($personas as $persona) {
      wp_schedule_single_event(
        time() + 10, // Slight delay to ensure post data is fully saved
        'cme_preload_persona_content',
        array(
          'post_id' => $post_id,
          'persona' => $persona
        )
      );
    }

    // Log the scheduling
    $this->log_preload_scheduling($post_id, count($personas));
  }

  // Generate and cache content for a specific post and persona
  public function preload_single_post($post_id, $persona) {
    // Start timing for performance metrics
    $start_time = microtime(true);

    // Generate content
    $content = $this->content_generator->generate_for_persona($post_id, $persona);

    // Store in cache
    $cache_key = $this->get_cache_key($post_id, $persona);
    $this->store_in_cache($cache_key, $content);

    // Track preload performance
    $duration = microtime(true) - $start_time;
    $this->record_preload_metrics($post_id, $persona, strlen($content), $duration);
  }
}
```

### Phase 4: Invalidation System

**Timeline: 2 Weeks**

**Deliverables:**
- Dependency tracking system for content relationships
- Selective invalidation based on content type
- Cache invalidation hooks for all content updates
- Emergency flush mechanisms

**Tasks:**
1. Implement dependency tracking for related content
2. Create selective invalidation system
3. Integrate with WordPress hooks for content updates
4. Develop emergency flush mechanism for admin use
5. Create logging and tracking for invalidation events

**Example Implementation:**
```php
class CME_Cruises_Cache_Invalidation {
  private $redis_manager;

  public function __construct() {
    $this->redis_manager = CME_Cruises_Redis_Manager::get_instance();

    // Register hooks for content updates
    add_action('save_post', array($this, 'invalidate_post_cache'), 10, 3);
    add_action('deleted_post', array($this, 'invalidate_post_cache'));
    add_action('edited_term', array($this, 'invalidate_term_cache'), 10, 3);
  }

  // Track dependencies between content items
  public function track_dependencies($primary_id, $dependent_ids) {
    if (!$this->redis_manager->is_available()) {
      return false;
    }

    $key = "cme:deps:post:{$primary_id}";
    return wp_cache_set($key, $dependent_ids, 'redis');
  }

  // Invalidate cache for a post and its dependencies
  public function invalidate_post_cache($post_id, $post = null, $update = null) {
    // Skip revisions, auto-saves, etc.
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
      return;
    }

    // Get all active personas
    $personas = $this->get_active_personas();

    // Invalidate cache for this post across all personas
    foreach ($personas as $persona) {
      $key = $this->get_cache_key($post_id, $persona);
      wp_cache_delete($key, 'redis');
    }

    // Check for dependencies and invalidate them
    $deps_key = "cme:deps:post:{$post_id}";
    $dependencies = wp_cache_get($deps_key, 'redis');

    if (is_array($dependencies)) {
      foreach ($dependencies as $dep_id) {
        $this->invalidate_post_cache($dep_id);
      }
    }

    // Log invalidation
    $this->log_cache_invalidation($post_id, count($personas));

    // Schedule reloading
    $this->schedule_post_preload($post_id);
  }
}
```

### Phase 5: Monitoring and Optimization

**Timeline: 1 Week**

**Deliverables:**
- Cache hit rate monitoring
- Memory usage tracking
- Performance metrics dashboard
- Admin tools for cache management

**Tasks:**
1. Implement cache statistics collection
2. Create admin dashboard for monitoring
3. Add memory usage tracking and alerts
4. Develop maintenance tools for cache management
5. Add performance reporting

**Example Implementation:**
```php
class CME_Cruises_Cache_Monitor {
  private $redis_manager;

  public function __construct() {
    $this->redis_manager = CME_Cruises_Redis_Manager::get_instance();

    // Add admin menu
    add_action('admin_menu', array($this, 'add_admin_menu'));
  }

  // Record cache hit/miss statistics
  public function record_cache_event($type, $key) {
    if (!$this->redis_manager->is_available()) {
      return;
    }

    $today = date('Y-m-d');

    // Increment hit/miss counter
    $counter_key = "cme:stats:{$today}:{$type}";
    $this->redis_manager->get_client()->incr($counter_key);

    // Set expiry for stats keys (30 days)
    $this->redis_manager->get_client()->expire($counter_key, 30 * DAY_IN_SECONDS);
  }

  // Get cache statistics for dashboard
  public function get_cache_stats() {
    if (!$this->redis_manager->is_available()) {
      return array(
        'status' => 'unavailable',
        'stats' => array()
      );
    }

    // Get memory info
    $info = $this->redis_manager->get_client()->info('memory');

    // Get hit/miss for last 7 days
    $stats = array();
    for ($i = 0; $i < 7; $i++) {
      $date = date('Y-m-d', time() - ($i * DAY_IN_SECONDS));

      $hits = (int)$this->redis_manager->get_client()->get("cme:stats:{$date}:hit") ?: 0;
      $misses = (int)$this->redis_manager->get_client()->get("cme:stats:{$date}:miss") ?: 0;

      $stats[$date] = array(
        'hits' => $hits,
        'misses' => $misses,
        'ratio' => ($hits + $misses > 0) ? ($hits / ($hits + $misses)) : 0
      );
    }

    return array(
      'status' => 'available',
      'memory' => array(
        'used' => isset($info['used_memory_human']) ? $info['used_memory_human'] : 'unknown',
        'peak' => isset($info['used_memory_peak_human']) ? $info['used_memory_peak_human'] : 'unknown',
        'limit' => isset($info['maxmemory_human']) ? $info['maxmemory_human'] : 'unknown',
      ),
      'stats' => $stats
    );
  }
}
```

## Key Technical Decisions

1. **Redis Key Schema:**
   - Use consistently structured keys: `cme:persona:{persona_id}:post:{post_id}:content`
   - Store metadata separate from content: `cme:meta:post:{post_id}:modified`
   - Track dependencies with separate keys: `cme:deps:post:{post_id}`

2. **Cache TTL Strategy:**
   - Primary content cache: 1 week (or until invalidated)
   - Dependency tracking: 1 month
   - Statistics: 30 days

3. **Performance Targets:**
   - Cache hit rate: >95%
   - TTFB: <50ms for cached content
   - Preload time per page: <1s per persona

4. **Fallback Strategy:**
   - On Redis failure: Generate content dynamically
   - On preloader failure: Schedule retry with exponential backoff

## Testing Strategy

1. **Unit Tests:**
   - Redis connection and error handling
   - Key generation and validation
   - Content generation for each persona

2. **Integration Tests:**
   - Full content flow from generation to retrieval
   - Invalidation propagation through dependencies
   - Preloader job scheduling and execution

3. **Performance Tests:**
   - Cache hit rate under load
   - Memory consumption with large content sets
   - Preloader throughput

4. **Environment-Specific Tests:**
   - Single Redis server configuration
   - Redis cluster configuration
   - Fallback behavior when Redis is unavailable

## Deployment Process

1. **Pre-Deployment:**
   - Backup existing Redis database (if upgrading)
   - Run performance baseline tests

2. **Deployment:**
   - Deploy infrastructure changes first
   - Deploy code changes
   - Run cache warming immediately after deployment

3. **Post-Deployment:**
   - Monitor cache hit rates and memory usage
   - Verify preloader is functioning
   - Run performance validation tests

## Maintenance Considerations

1. **Regular Tasks:**
   - Review cache hit rates weekly
   - Monitor memory usage trends
   - Adjust TTLs based on performance data

2. **Scaling Considerations:**
   - Implement Redis Cluster when content volume exceeds single instance capacity
   - Consider selective caching based on page popularity for very large sites
   - Implement tiered TTLs based on content update frequency

3. **Troubleshooting:**
   - Detailed logging of cache misses
   - Performance tracing for slow preloads
   - Admin tools for manual cache management
