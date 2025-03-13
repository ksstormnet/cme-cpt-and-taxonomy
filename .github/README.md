# GitHub Workflows

This directory contains the GitHub Actions workflows for the CME Personas plugin.

## Workflows

### 1. Deploy to Production (`deploy.yml`)

This workflow automates deployment to the production server.

- **Trigger**: When a pull request to the `main` branch is closed and merged
- **Condition**: Only runs if the PR was merged from the `dev` branch to `main`
- **Actions**:
  - Sets up SSH connections
  - Uses rsync to deploy files to the production server
  - Sets proper file permissions

### 2. Test & Lint (`test.yml`)

This workflow runs tests and linting on the codebase.

- **Trigger**: 
  - On pull requests to `dev` or `main` branches
  - On pushes to the `dev` branch
- **Actions**:
  - Sets up Node.js
  - Installs dependencies
  - Runs linting checks
  - Runs PHP CodeSniffer

## Deployment Strategy

1. All development work is done on feature branches
2. Feature branches are merged into the `dev` branch via pull requests
3. The `dev` branch is tested but not deployed automatically
4. When ready for production, `dev` is merged into `main` via a pull request
5. After the PR is merged, the deployment workflow automatically runs

This ensures that only thoroughly tested code that has been explicitly promoted to production (via a merge from `dev` to `main`) is deployed to the production server.
