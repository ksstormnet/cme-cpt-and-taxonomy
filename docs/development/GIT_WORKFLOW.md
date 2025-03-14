# Git Workflow Procedures

This document outlines the standard procedures for working with Git in the CME Personas plugin project. For general development practices, refer to [DEVELOPMENT_WORKFLOW.md](./DEVELOPMENT_WORKFLOW.md).

## Table of Contents

- [Git Workflow Procedures](#git-workflow-procedures)
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
