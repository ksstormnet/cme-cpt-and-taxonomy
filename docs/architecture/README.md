# Persona Content System Documentation

This directory contains comprehensive documentation for the persona content system. The documentation covers architectural decisions, implementation details, technical code examples, and performance optimization strategies.

## Documentation Roadmap

We recommend reading these documents in the following order:

1. **[Architecture Overview](./01-architecture-overview.md)** - Introduction to the persona system concepts and components
2. **[Implementation Approach](./02-implementation-approach.md)** - Implementation options and rationale for our chosen approach
3. **[Technical Reference](./03-technical-reference.md)** - Developer-focused implementation details with code examples
4. **[Performance Optimization](./04-performance-optimization.md)** - Performance strategies including Redis caching integration

## Key Architecture Decisions

### Boundary-Based Shortcode Approach

We've implemented the persona content system using a **boundary-based shortcode approach** for content personalization. This decision was made for several reasons:

- **Superior compatibility** with third-party plugins and content elements
- **Intuitive editing** for content authors through standard WordPress shortcodes
- **Flexible implementation** that works across all content areas
- **Performance benefits** through selective processing and caching

### Standalone WordPress Plugin

The system is implemented as a **standalone WordPress plugin** rather than embedding it within the Cruise Made Easy plugin. This approach offers:

- Better separation of concerns
- Improved modularity and reusability
- Clear API boundaries for integration
- Simplified maintenance and updates

## Persona Content System Overview

The persona content system enables websites to deliver tailored content to different visitor types using WordPress shortcodes. Each visitor is identified as belonging to a specific "persona" or visitor archetype, and content is dynamically shown or hidden based on the active persona.

### Supported Personas

The system currently supports the following personas:

- **Default** - Standard content shown to unidentified visitors
- **Easy-Breezy Cruiser** - Casual cruisers looking for relaxation and simplicity
- **Luxe Seafarer** - Luxury-focused travelers seeking premium experiences
- **Thrill Seeker** - Adventure-oriented cruisers interested in activities and exploration

### Key Features

- Persona detection from GTM/cookies/URL parameters
- Intuitive shortcode-based content personalization
- Admin UI for previewing content per persona
- Block editor integration for convenient shortcode insertion
- Performance optimization through Redis and edge caching

## Implementation Status

The implementation is being rolled out in phases:

- [x] **Phase 1:** Core Infrastructure - Completed
- [ ] **Phase 2:** Shortcode System - In Progress
- [ ] **Phase 3:** Admin Integration
- [ ] **Phase 4:** Performance Optimization

See [Performance Optimization](./04-performance-optimization.md) for detailed implementation phases and status.

## For Developers

If you're developing with or extending the persona system:

- Review the [Technical Reference](./03-technical-reference.md) for code examples and API details
- Explore available hooks and filters for customization
- Check the [Performance Optimization](./04-performance-optimization.md) guide for caching implementation

## Redis & Shortcode Compatibility

The Redis object caching implementation is complementary to the boundary-based shortcode approach, not an alternative. Redis caching is used to optimize performance by:

- Caching processed shortcode output for each persona
- Reducing repeated shortcode processing overhead
- Minimizing database queries for persona detection

This creates a two-level caching strategy:

1. Redis as an object/fragment cache at the WordPress level
2. Edge caching (Cloudflare) at the network level

For detailed information on this integration, see the [Performance Optimization](./04-performance-optimization.md) document.
