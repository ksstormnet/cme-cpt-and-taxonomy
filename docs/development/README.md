# Development Documentation

This directory contains documentation related to the development process, coding standards, and workflow for the CME Personas plugin.

## CRITICAL PRACTICES - READ FIRST

1. **NEVER modify ANY repository content without creating a branch first**

   - All changes require a feature branch from `dev`
   - No exceptions for "small" or "quick" changes
   - See [Git Workflow](./GIT_WORKFLOW.md) for details

2. **CONTINUOUSLY update documentation as you work**

   - Documentation should be updated alongside code changes
   - Keep checklists and status documents current
   - Don't defer documentation updates until version increments

3. **QUESTION unclear instructions rather than making assumptions**
   - Always seek clarification when requirements seem ambiguous
   - Provide options when multiple approaches are possible
   - See [AI Assistants Guidelines](./AI_ASSISTANTS.md) for more guidance

## Documentation Index

- [Coding Standards](./CODING_STANDARDS.md) - Detailed standards for PHP, JavaScript, CSS, and Markdown
- [Development Workflow](./DEVELOPMENT_WORKFLOW.md) - Standard development processes and coding practices
- [Git Workflow](./GIT_WORKFLOW.md) - Git procedures, branching strategy, and semantic versioning
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
7. **Suggest appropriate version increments** - Based on semantic versioning guidelines
8. **Branch from dev, not main** - All development work starts from the `dev` branch

## Quick Reference

```bash
# CREATE BRANCH (REQUIRED FOR ANY CHANGES)
git checkout dev
git pull origin dev
git checkout -b feature/my-feature-name

# Run linters
npm run lint                  # Check all file types
npm run lint:js               # Check JavaScript files
npm run lint:css              # Check CSS files
npm run lint:php              # Check PHP files
npm run lint:md               # Check Markdown files

# Fix linting issues
npm run fix:js                # Fix JavaScript files
npm run fix:css               # Fix CSS files
npx markdownlint-cli2-fix .   # Fix Markdown files

# Create a PR when done
git push -u origin feature/my-feature-name
gh pr create --base dev --head feature/my-feature-name --title "My Feature Title"
```

Refer to the specific documentation files for more detailed information on each topic.
