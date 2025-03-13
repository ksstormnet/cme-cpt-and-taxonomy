# Development Workflow for CME Personas

This document outlines the development workflow and practices for the CME Personas plugin.

## Table of Contents

- [Development Workflow for CME Personas](#development-workflow-for-cme-personas)
  - [Table of Contents](#table-of-contents)
  - [Code Quality Standards](#code-quality-standards)
  - [Development Workflow](#development-workflow)
    - [1. Branching Strategy](#1-branching-strategy)
    - [2. Development Process](#2-development-process)
    - [3. Code Linting and Quality](#3-code-linting-and-quality)
    - [4. Commit Guidelines](#4-commit-guidelines)
    - [5. Pull Request Process](#5-pull-request-process)
  - [Linting Tools](#linting-tools)
    - [Running Linters](#running-linters)
    - [Fixing Linting Issues](#fixing-linting-issues)
  - [Pre-commit Hooks](#pre-commit-hooks)
  - [Common Best Practices](#common-best-practices)

## Code Quality Standards

The CME Personas plugin follows strict code quality standards. All code must adhere to these standards to ensure consistency, maintainability, and robustness. For specific coding standards, refer to the [CODING_STANDARDS.md](./CODING_STANDARDS.md) document.

## Development Workflow

### 1. Branching Strategy

- `main` - Production-ready code
- `dev` - Development branch that contains the latest approved features
- Feature branches - Used for individual feature development

Always create a new branch for each feature, bug fix, or task. Branch names should follow this convention:

- `feature/` - For new features (e.g., `feature/add-content-personalization`)
- `bugfix/` - For bug fixes (e.g., `bugfix/fix-image-display`)
- `chore/` - For maintenance tasks (e.g., `chore/update-dependencies`)
- `docs/` - For documentation updates (e.g., `docs/update-readme`)

```bash
# Example: Creating a new feature branch from dev
git checkout dev
git pull origin dev
git checkout -b feature/new-feature-name
```

### 2. Development Process

1. Ensure your local `dev` branch is up to date
2. Create a new branch for your task
3. Implement your changes
4. Test your changes thoroughly
5. Run linters and fix any issues
6. Commit your changes with descriptive messages
7. Push your branch and create a PR

### 3. Code Linting and Quality

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

**❌ Never use `--no-verify` to bypass pre-commit hooks unless explicitly instructed** by a senior developer or team lead.

### 4. Commit Guidelines

- Make frequent, small commits rather than large, infrequent ones
- Write clear, descriptive commit messages
- Follow the conventional commit format:
  - `feat:` - A new feature
  - `fix:` - A bug fix
  - `docs:` - Documentation changes
  - `style:` - Code style changes (formatting, etc.)
  - `refactor:` - Code changes that neither fix bugs nor add features
  - `test:` - Adding or updating tests
  - `chore:` - Changes to the build process, tooling, etc.

```bash
# Examples
git commit -m "feat: add persona detection system"
git commit -m "fix: resolve issue with gender-specific images"
git commit -m "docs: update technical implementation documentation"
```

### 5. Pull Request Process

When your feature is complete, create a pull request using GitHub CLI:

```bash
# Push your branch to the remote repository
git push -u origin your-branch-name

# Create a pull request using the GitHub CLI
gh pr create --base dev --head your-branch-name --title "Your PR title" --body "Description of your changes"
```

Your PR should include:

- A clear title describing the change
- A detailed description of what was changed and why
- Any relevant issue numbers (e.g., "Fixes #123")
- Screenshots or examples if applicable

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

## Common Best Practices

1. **Write code that meets standards from the start**: Understand the linting rules and write code that adheres to them initially, rather than fixing issues later.

2. **Keep branches small and focused**: Each branch should address a single feature, bug fix, or task.

3. **Update documentation as you go**: When you change functionality, update the relevant documentation in the same PR.

4. **Test thoroughly**: Ensure your changes work correctly before creating a PR.

5. **Review your own code first**: Before requesting a review, look through your changes for obvious issues.

6. **Respond to PR feedback promptly**: Address reviewer comments in a timely manner.

7. **Keep dependencies updated**: Regularly check for outdated dependencies and update them.

By following these practices, we maintain a high-quality codebase and efficient development process.
