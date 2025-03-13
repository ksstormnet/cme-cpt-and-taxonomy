# CME Cruises Documentation

This directory contains the official documentation for the CME Cruises WordPress plugin version 0.2.0 and above.

## Documentation Structure

- **[Architecture](./architecture/)**: System design, data models, component interactions, and integration plans
- **[Development](./development/)**: Development workflows, standards, and procedures
- **[Guides](./guides/)**: Feature-specific guides and tutorials
- **[Personas](./personas/)**: Documentation for the standalone Personas plugin

## Core Features

The CME Cruises plugin provides the following core functionality:

1. **Cruise Management**: Store and present cruise itineraries with deck plans, ports of call, and pricing
2. **Ship Profiles**: Detailed information about cruise ships including amenities and specifications
3. **Destination Information**: Rich content about cruise destinations with local information
4. **Persona Content System**: Tailored content for different visitor types/personas
5. **NCL Content Scraper**: Automated scraping and importing of cruise information

## Personas Implementation

The Personas functionality is being developed as a standalone WordPress plugin for better separation of concerns and reusability. Key documentation:

- **[Personas Plugin Implementation Plan](./personas/PLUGIN_IMPLEMENTATION.md)**: Design for the standalone plugin
- **[Integration Plan](./architecture/PERSONAS_INTEGRATION_PLAN.md)**: How Cruise Made Easy integrates with the Personas plugin

## Version Information

This documentation applies to CME Cruises v0.2.0 and later. For historical documentation relating to earlier versions, see the `/legacy/docs` directory.

## Getting Started

For new developers, we recommend starting with:

1. [Architecture Overview](./architecture/OVERVIEW.md) - Understand the system design
2. [Development Workflow](./development/WORKFLOW.md) - Set up your development environment
3. [Persona System Guide](./guides/PERSONAS.md) - Learn about the persona content system

## Contributing to Documentation

When contributing to this documentation:

1. Use clear, concise language
2. Include code examples when applicable
3. Follow the Markdown formatting standards
4. Update table of contents when adding new sections

## License

This documentation and the CME Cruises plugin are proprietary to Sky+Sea LLC d/b/a KSStorm Media.
