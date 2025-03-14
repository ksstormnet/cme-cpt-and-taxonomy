# Persona Content System Documentation

This directory contains comprehensive documentation for the persona content system. The documentation covers architectural decisions, implementation details, and integration plans.

## Documentation Index

- [Overview](./OVERVIEW.md) - Introduction to the persona content system, its features, and capabilities
- [Implementation](./IMPLEMENTATION.md) - Comprehensive overview of implementation approaches and the chosen WordPress plugin implementation
- [Technical Implementation](./TECHNICAL_IMPLEMENTATION.md) - Developer-focused implementation details with code examples
- [Redis Implementation Plan](./IMPLEMENTATION_PLAN.md) - Alternative implementation using Redis-based caching (kept for reference)

## Implementation Decision

**Current Direction**: Based on our latest analysis, we've decided to implement personas as a **standalone WordPress plugin** rather than embedding it within the Cruise Made Easy plugin. This approach offers better separation of concerns, modularity, and reusability.

The implementation details are found in the [Implementation](./IMPLEMENTATION.md) document, which includes a comparison of approaches and the detailed plan for the chosen WordPress plugin architecture.

## Purpose

The persona content system enables websites to deliver tailored content to different visitor types. Each visitor is identified as belonging to a specific "persona" or visitor archetype, and content is dynamically customized to match their preferences and needs.

## Supported Personas

The system currently supports the following personas:

- **Default** - Standard content shown to unidentified visitors
- **Easy-Breezy Cruiser** - Casual cruisers looking for relaxation and simplicity
- **Luxe Seafarer** - Luxury-focused travelers seeking premium experiences
- **Thrill Seeker** - Adventure-oriented cruisers interested in activities and exploration

## Key Features

- Persona detection from GTM/cookies
- Content storage and retrieval system using WordPress post meta
- Block-level content customization
- Admin UI for managing persona-specific content
- Integration with the Gutenberg block editor
- Optimized performance with optional Redis caching

## Getting Started

For developers new to the project, we recommend:

1. First reading the [Overview](./OVERVIEW.md) document
2. Then review the [Implementation](./IMPLEMENTATION.md) document
3. For technical code examples, see [Technical Implementation](./TECHNICAL_IMPLEMENTATION.md)
