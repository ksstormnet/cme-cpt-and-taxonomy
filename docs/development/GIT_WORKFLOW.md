# Git Workflow Procedures

This document outlines the standard procedures for working with Git in the CME Personas plugin project. For general development practices, refer to [DEVELOPMENT_WORKFLOW.md](./DEVELOPMENT_WORKFLOW.md).

## CRITICAL PRACTICES - READ FIRST

1. **NEVER modify ANY repository content without creating a branch first**

   - This includes code, documentation, configuration files, assets, etc.
   - No exceptions for "small" or "quick" changes
   - Always follow the branching strategy below for all changes

2. **ALWAYS branch from `dev`, not from `main`**

   - All development work starts from the `dev` branch
   - Only release processes should interact directly with `main`

3. **CONTINUOUSLY update documentation as you work**

   - Update documentation alongside code changes, not at the end
   - Keep checklists current as tasks are completed
   - Don't defer documentation updates until version increments

4. **USE appropriate semantic versioning**
   - MAJOR version (x.0.0): Breaking changes that are not backward compatible
   - MINOR version (0.x.0): New features added in a backward compatible manner
   - PATCH version (0.0.x): Backward compatible bug fixes and non-functional changes

## Table of Contents

- [Git Workflow Procedures](#git-workflow-procedures)
  - [CRITICAL PRACTICES - READ FIRST](#critical-practices---read-first)
  - [Table of Contents](#table-of-contents)
  - [Branch Structure](#branch-structure)
  - [Regular Commit Process](#regular-commit-process)
    - [1. Development on Feature Branches](#1-development-on-feature-branches)
    - [2. Commit Guidelines](#2-commit-guidelines)
    - [3. Create Pull Request](#3-create-pull-request)
    - [4. After PR is Merged](#4-after-pr-is-merged)
  - [Version Increment Process](#version-increment-process)
    - [1. Ensure Clean Working Directory](#1-ensure-clean-working-directory)
    - [2. Update Version Numbers](#2-update-version-numbers)
      - [For Simple Patch Version Increment](#for-simple-patch-version-increment)
      - [For Custom Version Set](#for-custom-version-set)
    - [3. Update Files With New Version](#3-update-files-with-new-version)
    - [4. Commit Version Changes](#4-commit-version-changes)
    - [5. Create Release Tags](#5-create-release-tags)
    - [6. Push Changes to Remote](#6-push-changes-to-remote)
    - [7. Check Deployment Status](#7-check-deployment-status)
  - [Essential Git Practices](#essential-git-practices)
  - [Version Increment Guidelines](#version-increment-guidelines)
    - [When to Use MAJOR Version (x.0.0)](#when-to-use-major-version-x00)
    - [When to Use MINOR Version (0.x.0)](#when-to-use-minor-version-0x0)
    - [When to Use PATCH Version (0.0.x)](#when-to-use-patch-version-00x)

## Branch Structure

The project uses the following branch structure:

- `main` - Production-ready code
- `dev` - Primary development branch
- Feature branches:
  - `feature/*` - Feature development (e.g., `feature/add-persona-detection`)
  - `bugfix/*` - Bug fixes (e.g., `bugfix/fix-image-display`)
  - `chore/*` - Maintenance tasks (e.g., `chore/update-dependencies`)
  - `docs/*` - Documentation updates (e.g., `docs/update-readme`)

## Regular Commit Process

### 1. Development on Feature Branches

```bash
# Make sure you're on dev and it's up to date
git checkout dev
git pull origin dev

# Create new feature branch
git checkout -b feature/my-feature-name
```

### 2. Commit Guidelines

Format commit messages using conventional commit style:

```bash
# Format
git commit -m "<type>: <description>"

# Examples
git commit -m "feat: add persona detection system"
git commit -m "fix: resolve issue with gender-specific images"
git commit -m "docs: update technical implementation documentation"
```

Commit types:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code change that neither fixes a bug nor adds a feature
- `perf:` - Performance improvements
- `test:` - Adding or correcting tests
- `chore:` - Changes to the build process or auxiliary tools

### 3. Create Pull Request

```bash
# Push the branch to remote
git push -u origin feature/my-feature-name

# Create PR using GitHub CLI
gh pr create --base dev --head feature/my-feature-name --title "My Feature Title" --body "Description of your changes"
```

PR content requirements:

- A clear title describing the change
- A detailed description of what was changed and why
- Any relevant issue numbers (e.g., "Fixes #123")
- Screenshots or examples if applicable

### 4. After PR is Merged

```bash
# Switch back to dev
git checkout dev

# Update local dev branch
git pull origin dev

# Clean up local feature branch
git branch -d feature/my-feature-name
```

## Version Increment Process

### 1. Ensure Clean Working Directory

```bash
# Check for uncommitted changes
git status

# Make sure you're on the main branch
git checkout main
git pull origin main
```

Ensure there are no uncommitted changes before proceeding.

### 2. Update Version Numbers

#### For Simple Patch Version Increment

To increment patch version (e.g., 1.0.0 → 1.0.1):

```bash
# Determine current version from plugin file
CURRENT_VERSION=$(grep -o "Version: *[0-9]\+\.[0-9]\+\.[0-9]\+" "cme-personas.php" | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")

# Calculate new version
IFS='.' read -r -a version_parts <<< "${CURRENT_VERSION}"
NEW_MAJOR="${version_parts[0]}"
NEW_MINOR="${version_parts[1]}"
NEW_PATCH=$((version_parts[2] + 1))
NEW_VERSION="${NEW_MAJOR}.${NEW_MINOR}.${NEW_PATCH}"

echo "Current version: ${CURRENT_VERSION}"
echo "New version: ${NEW_VERSION}"
```

#### For Custom Version Set

For manual version changes (e.g., for minor or major releases):

```bash
# Set version manually (replace with desired version)
NEW_VERSION="1.1.0"
```

### 3. Update Files With New Version

Find and update version numbers in all relevant files:

```bash
# Find files with version patterns
find . -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.json" -o -name "*.txt" -o -name "*.md" \) -not -path "*/node_modules/*" -not -path "*/vendor/*" -not -path "*/.git/*" -exec grep -l "[0-9]\+\.[0-9]\+\.[0-9]\+" {} \;
```

Then update version numbers in:

- Plugin header: `* Version: x.y.z`
- Constants: `const VERSION = 'x.y.z'`
- Define statements: `define('PLUGIN_VERSION', 'x.y.z')`
- Variable assignments: `$version = 'x.y.z'`
- README.md: `**Version:** x.y.z`
- Composer.json: `"version": "x.y.z"`

### 4. Commit Version Changes

```bash
# Commit version changes
git add -A
git commit -m "chore: bump version to ${NEW_VERSION}"
```

### 5. Create Release Tags

```bash
# Create tags both with and without v prefix
git tag -a "${NEW_VERSION}" -m "Release ${NEW_VERSION}"
git tag -a "v${NEW_VERSION}" -m "Release ${NEW_VERSION}"
```

### 6. Push Changes to Remote

```bash
# Push commits and tags
git push origin main
git push origin "${NEW_VERSION}" "v${NEW_VERSION}"
```

### 7. Check Deployment Status

After pushing the tags, check GitHub Actions to ensure the deployment workflow was triggered:

```bash
# Repo URL for actions
GITHUB_REPO_URL=$(git config --get remote.origin.url | sed 's/git@github.com:/https:\/\/github.com\//' | sed 's/\.git$//')
echo "Check deployment status at: ${GITHUB_REPO_URL}/actions"
```

## Essential Git Practices

1. **Use semantic versioning (x.y.z)** for all version numbers
2. **Ensure clean working directory** before releases
3. **Verify tags don't already exist** before creating new ones
4. **Make small, focused commits** rather than large infrequent ones
5. **Keep feature branches short-lived** - merge or delete them promptly
6. **Rebase feature branches** on dev before creating PRs when there are conflicts
7. **Use meaningful commit messages** that clearly describe the changes
8. **Never push directly to main or dev** branches - always use PRs
9. **Never rewrite history** of shared branches (main/dev)
10. **Follow the process** - don't take shortcuts in the git workflow
11. **Suggest version increments** when appropriate for your changes
12. **Update documentation continuously** as you implement changes

## Version Increment Guidelines

When determining or suggesting version increments, follow these guidelines:

### When to Use MAJOR Version (x.0.0)

- Breaking API changes
- Incompatible changes to database schemas
- Removing deprecated functionality
- Changes requiring users to modify their implementation

### When to Use MINOR Version (0.x.0)

- Adding new features while maintaining backward compatibility
- Marking functionality as deprecated (but still available)
- Substantial internal refactoring that doesn't break compatibility
- Adding new files or modules with new functionality

### When to Use PATCH Version (0.0.x)

- Bug fixes without API changes
- Performance improvements without API changes
- Non-functional changes like formatting, style, and documentation
- Changes to error messages or logs

If in doubt about which version increment to use, err on the side of a higher increment level to avoid underestimating the impact of changes.
