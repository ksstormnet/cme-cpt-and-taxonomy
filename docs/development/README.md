# Development Documentation

This directory contains documentation related to the development process, coding standards, and workflow for the CME Personas plugin.

## Documentation Index

- [Coding Standards](./CODING_STANDARDS.md) - Detailed standards for PHP, JavaScript, CSS, and Markdown
- [Development Workflow](./DEVELOPMENT_WORKFLOW.md) - Standard development process and workflow
- [Git Workflow](./GIT_WORKFLOW.md) - Git procedures, commit standards, and version management
- [AI Assistants Guidelines](./AI_ASSISTANTS.md) - Guidelines for working with AI programming assistants
- [Security](./SECURITY.md) - Security considerations and best practices

## Important Guidelines

When working on this project, follow these key principles:

1. **Adhere to coding standards** - All code must meet the defined standards before committing
2. **Follow proper workflow** - Create feature branches, make focused commits, and create proper PRs
3. **Never bypass quality checks** - Don't use `--no-verify` to bypass pre-commit hooks
4. **Remove obsolete code** - Delete unused code rather than commenting it out
5. **Only implement necessary features** - Don't include speculative migrations or features
6. **Keep documentation updated** - Update docs alongside code changes

## Quick Reference

```bash
# Check all file types with linters
npm run lint

# Run specific linters
npm run lint:js
npm run lint:css
npm run lint:php
npm run lint:md

# Attempt to auto-fix issues
npm run fix:js
npm run fix:css
```

Refer to the specific documentation files for more detailed information on each topic.
