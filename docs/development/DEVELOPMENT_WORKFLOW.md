# Development Workflow for CME Personas

This document outlines the development process and coding practices for the CME Personas plugin.

## Table of Contents

- [Development Workflow for CME Personas](#development-workflow-for-cme-personas)
  - [Table of Contents](#table-of-contents)
  - [Code Quality Standards](#code-quality-standards)
  - [Development Process Overview](#development-process-overview)
  - [Code Linting and Quality](#code-linting-and-quality)
  - [Linting Tools](#linting-tools)
    - [Running Linters](#running-linters)
    - [Fixing Linting Issues](#fixing-linting-issues)
  - [Pre-commit Hooks](#pre-commit-hooks)
  - [Common Pitfalls to Avoid](#common-pitfalls-to-avoid)
    - [Bypassing Linting](#bypassing-linting)
    - [Migration Restrictions](#migration-restrictions)
    - [Code Removal Best Practices](#code-removal-best-practices)
  - [Common Best Practices](#common-best-practices)
  - [Working with AI Assistants](#working-with-ai-assistants)

## Code Quality Standards

The CME Personas plugin follows strict code quality standards. All code must adhere to these standards to ensure consistency, maintainability, and robustness. For specific coding standards, refer to the [CODING_STANDARDS.md](./CODING_STANDARDS.md) document.

## Development Process Overview

1. **Plan and understand requirements** before starting development
2. **Create a feature branch** following the naming conventions in [GIT_WORKFLOW.md](./GIT_WORKFLOW.md)
3. **Implement your changes** following coding standards
4. **Write and run tests** to ensure functionality
5. **Run linters** and fix any issues
6. **Commit your changes** with descriptive messages (see [GIT_WORKFLOW.md](./GIT_WORKFLOW.md) for commit guidelines)
7. **Create a pull request** for code review (see [GIT_WORKFLOW.md](./GIT_WORKFLOW.md) for PR process)
8. **Address feedback** from code reviews
9. **Merge** once approved

For detailed git procedures, branch naming conventions, and PR creation process, refer to [GIT_WORKFLOW.md](./GIT_WORKFLOW.md).

## Code Linting and Quality

**All code must be linted before committing**. The project uses multiple linters to ensure code quality:

- PHP: PHPCodeSniffer
- JavaScript: ESLint
- CSS: Stylelint
- Markdown: Markdownlint

**✅ Always run linters at the end of each task** and fix any issues before committing:

```bash
# Check all file types at once
npm run lint

# Check individual file types
npm run lint:js
npm run lint:css
npm run lint:php
npm run lint:md
```

**✅ When linters identify issues that cannot be auto-fixed, you must manually fix them before committing.**

**❌ Never use `--no-verify` to bypass pre-commit hooks** unless explicitly instructed by a senior developer or team lead.

## Linting Tools

The project uses the following linting tools:

- **PHP**: PHPCodeSniffer (phpcs)
- **JavaScript**: ESLint with WordPress configuration
- **CSS**: Stylelint with WordPress configuration
- **Markdown**: Markdownlint with custom rules

### Running Linters

```bash
# Run all linters
npm run lint

# Run individual linters
npm run lint:js
npm run lint:css
npm run lint:php
npm run lint:md
```

### Fixing Linting Issues

Some linting issues can be automatically fixed:

```bash
# Attempt to fix ESLint issues
npx eslint --fix path/to/file.js

# Attempt to fix Stylelint issues
npx stylelint --fix path/to/file.css

# Attempt to fix Markdown issues
npx markdownlint-cli2-fix path/to/file.md
```

For issues that cannot be automatically fixed, you will need to manually resolve them according to the error messages.

## Pre-commit Hooks

The project uses Husky to run pre-commit hooks that enforce coding standards:

1. When you commit, `lint-staged` will run linters on the files you've changed
2. If linting issues are found, the commit will be rejected
3. Fix the issues and try committing again

**Remember: Never use `--no-verify` to bypass these hooks** unless explicitly instructed to do so.

## Common Pitfalls to Avoid

### Bypassing Linting

❌ **NEVER DO THIS:**

```bash
git commit --no-verify -m "message"
```

This bypasses our quality control and introduces technical debt. Instead:

✅ **DO THIS:**

1. Run `npm run lint` before committing
2. Fix issues identified by linters
3. Commit clean code that passes all checks

### Migration Restrictions

❌ **NEVER DO THIS:**

- Creating migration files without schema changes
- Adding placeholder migrations "for future use"
- Mentioning or suggesting migrations without being asked
- Including comments about "potential migrations needed"
- Speculating about database changes that might be required

✅ **DO THIS:**

- Only discuss migrations when explicitly asked
- Only create migrations for actual database changes when requested
- Focus on solving problems without assuming database interaction needs
- Request clarification if unsure about database interaction needs

### Code Removal Best Practices

❌ **NEVER DO THIS:**

```php
// Commented out but left in the codebase
// function oldFunction() {
//    // Implementation
// }

/**
 * @deprecated since version 1.2.0, use newFunction() instead.
 */
function oldFunction() {
    // Still in the codebase
}
```

✅ **DO THIS:**

```php
// Clean removal - not present in the code at all
// Document removal in commit message and/or changelog
```

## Common Best Practices

1. **Write code that meets standards from the start**: Understand the linting rules and write code that adheres to them initially, rather than fixing issues later.

2. **Keep features small and focused**: Each feature should address a specific requirement or task.

3. **Update documentation continuously**: Documentation is a first-class citizen in our codebase:

   - Update documentation alongside code changes, not after completing them
   - Keep user-facing guides in sync with implementation changes
   - Update checklists and task tracking as you complete each item
   - Ensure API documentation reflects the current behavior

4. **Test thoroughly**: Ensure your changes work correctly before requesting review.

5. **Review your own code first**: Before requesting a review, look through your changes for obvious issues.

6. **Respond to review feedback promptly**: Address reviewer comments in a timely manner.

7. **Keep dependencies updated**: Regularly check for outdated dependencies and update them.

8. **Suggest appropriate version increments**: Based on the nature of your changes, suggest the appropriate semantic version increment (see [GIT_WORKFLOW.md](./GIT_WORKFLOW.md) for guidelines).

9. **Question unclear requirements**: Don't make assumptions when requirements are unclear. Ask specific questions to clarify expectations.

By following these practices, we maintain a high-quality codebase and efficient development process.

## Working with AI Assistants

When using AI assistants (like Claude) for development tasks, refer to the [AI Assistants Guidelines](./AI_ASSISTANTS.md) for specific requirements and best practices.

Key points:

- Ensure AI-generated code adheres to our standards
- Never use `--no-verify` with AI-assisted commits
- Follow strict guidelines for code removal and migrations
- Provide proper context when working with AI tools
