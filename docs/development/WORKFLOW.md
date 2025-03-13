# Development Workflow

This document outlines the development workflow for the CME Cruises plugin.

## Development Environment

### Local Development Setup

1. **Docker-based WordPress Environment**

    - Local WordPress container with the plugin directory mounted
    - Live updates reflected immediately in the development environment
    - No need to rebuild or reinstall the plugin after each change

2. **Required Tools**
    - Docker & Docker Compose
    - Git
    - Composer
    - Node.js & npm

### Directory Structure

```plaintext
cme-cruises/
├── admin/               # Admin interfaces
│   ├── css/             # Admin stylesheets
│   ├── js/              # Admin scripts
│   └── partials/        # Admin templates
├── includes/            # Core functionality
│   ├── db/              # Database operations
│   ├── scraper/         # Scraper components
│   └── persona/         # Persona system
├── public/              # Frontend assets
│   ├── css/             # Frontend styles
│   ├── js/              # Frontend scripts
│   └── templates/       # Page templates
├── docs/                # Documentation
│   ├── architecture/    # System design docs
│   ├── development/     # Development guides
│   └── guides/          # Feature-specific guides
├── legacy/              # Deprecated code (not loaded)
└── tools/               # Helper scripts
```

## Git Workflow

See [Git Workflow Procedures](GIT_WORKFLOW.md) for detailed instructions on Git operations including:

- Branch creation and management
- Commit procedures and message formats
- Pull request creation
- Version increment procedures
- Release tagging
- Post-merge cleanup

### Branch Structure

- **main**: Production-ready code
- **dev**: Primary development branch
- **feature/[feature-name]**: Individual feature development
- **bugfix/[issue-number]**: Bug fixes

## Development Process

### 1. Feature Development

1. Create a feature branch from `dev`
2. Implement the feature with unit tests
3. Submit a pull request to `dev`
4. Code review and approval
5. Merge to `dev`

### 2. Local Testing

1. Mount the plugin directory in your local WordPress container
2. Changes are immediately reflected for testing
3. Test across multiple browsers and devices
4. Test with different personas active

### 3. Deployment

1. Merge `dev` to `main` for production release
2. GitHub Actions workflow runs deployment to production server
3. Version number is bumped according to changes

## Testing Guidelines

### WordPress Compatibility Testing

- Test with minimum supported WordPress version (5.9+)
- Test with latest WordPress version
- Test with classic and block editor

### PHP Compatibility Testing

- Target PHP 7.4+ compatibility
- Unit tests should pass on all supported PHP versions

### Frontend Testing

- Test with latest versions of major browsers
- Ensure responsive design works on mobile devices
- Test with personas enabled and switched

## Code Quality Tools

### Linting

- PHP_CodeSniffer with WordPress standards
- ESLint for JavaScript
- Stylelint for CSS

### Pre-commit Hooks

- In development environment, pre-commit hooks are disabled for iterative work
- CI enforces linting standards before merging to `main`

## Release Process

### Version Numbering

- Follow semantic versioning (MAJOR.MINOR.PATCH)
- `v0.x.y`: Development versions
- `v1.0.0`: First stable release

### Version Updates

1. Update version number in plugin header
2. Update CHANGELOG.md
3. Tag release in Git
4. Build release assets
5. Deploy to production

## Troubleshooting

### Common Issues

- **Plugin Activation Errors**: Check WordPress debug log for details
- **Database Migration Issues**: Use the `wp cme-cruises diagnose` CLI command
- **JavaScript Console Errors**: Check browser developer tools

### Support Resources

- Internal documentation
- GitHub issues
- Development Slack channel
