# Changelog

All notable changes to the Howard OpenID Connect Windows AAD module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [11.0.7] - 2025-10-23

### Added

- New update function `howard_openid_connect_windows_aad_update_11006()` for v11.0.6 compatibility improvements
- Enhanced cache clearing and plugin reloading during updates
- Improved update documentation and logging

### Changed

- Renamed update functions from `update_9001` and `update_9002` to `update_11001` and `update_11002` to align with Drupal 11.x versioning conventions
- Updated install file version documentation

### Technical

- Added cache clearing for plugin manager to ensure updated definitions are loaded
- Enhanced route rebuilding during module updates
- Comprehensive cache flushing to ensure compatibility with OpenID Connect 3.x changes
- Proper update function numbering for Drupal 11.x compatibility

### Maintenance

- Improved update hooks for better module upgrade experience
- Enhanced logging for troubleshooting update processes
- Aligned update function naming with module version

## [11.0.6] - 2025-10-22

### Changed

- Updated OpenID Connect dependency from `^3.0.0-alpha` to `^3.0` for broader compatibility
- Updated module dependency in info.yml from `(>=3.0.0-alpha)` to `(>=3.0)`
- Improved compatibility with stable releases of OpenID Connect module
- Fixed route parameter name in HowardWindowsAad plugin (`client_name` to `openid_connect_client`)
- Updated return type for error handling in `retrieveTokens()` method to return `null` instead of `false`

### Technical

- Better alignment with semantic versioning for dependency management
- Enhanced compatibility with OpenID Connect 3.x stable releases
- Improved error handling consistency across the module
- Fixed deprecated route parameter usage

## [11.0.5] - 2025-10-06

### Fixed

- Added missing dependency `drupal/openid_connect: ^3.0.0-alpha` to composer.json
- Fixed PHPCS coding standards violations
- Corrected line length issues in source files
- Added proper type checking for AccountInterface in WindowsAadSSOController
- Fixed Drupal static calls in HowardWindowsAadSSORouteSubscriber
- Improved code comments and formatting for better readability

### Changed

- Updated composer.json to include openid_connect module dependency
- Enhanced error handling with proper type checking
- Improved code quality and standards compliance
- Updated documentation formatting

### Technical

- Module now properly declares its dependency on openid_connect module
- Fixed type mismatches in session management
- Improved dependency injection patterns
- Code now passes basic quality checks

## [11.0.4] - 2025-07-24

### Security

- Updated dependency on openid_connect to require >=3.0.0-alpha in composer.json and info.yml
- Bumped version to 11.0.4 for release
- 6d44939 Updates to function with openid_connect:^3.0@alpha - requires site_shared_settings to be updated before release

## [11.0.3] - 2025-07-08

### Infrastructure

- Updated module configuration for Packagist distribution instead of drupal.org
- Removed drupal.org specific metadata from info.yml file (project and datestamp fields)
- Removed drupal-specific version metadata from composer.json extra section
- Added explicit version field to composer.json for proper Packagist versioning
- Module is now properly configured as a custom module for Packagist distribution

## [11.0.2] - 2025-07-07

### Added

- Comprehensive documentation structure with professional-grade docs
- Developer guide with testing and debugging information
- API documentation with technical specifications
- Installation guide with Azure AD setup instructions
- Release checklist and coding standards documentation
- Enhanced code documentation and comments throughout all PHP files
- Professional README.md with badges, features, and quick start guide
- Complete composer.json with dependencies, scripts, and metadata
- Session manager dependency injection for modern logout handling

### Code Quality

- Enhanced code documentation and comments in all PHP source files
- Improved error handling and logging throughout the module
- Updated configuration schema validation
- Converted README.txt to comprehensive README.md
- Updated module metadata in info.yml file
- Enhanced composer.json with professional structure and dependencies
- Replaced deprecated `user_logout()` calls with modern session management
- Improved dependency injection in WindowsAadSSOController

### Fixed

- PHPCS code style violations and warnings
- Deprecated function calls replaced with modern Drupal APIs
- Line length issues and formatting problems
- Translatable string formatting
- File documentation headers and structure
- Whitespace and trailing space issues

### Security

- Modernized authentication handling with session manager
- Improved logout security with proper session regeneration
- Enhanced validation and security checks

### Enhanced

- Plugin architecture documentation with comprehensive API coverage
- Controller documentation with security considerations
- Route subscriber documentation with dynamic routing explanations
- Module file documentation with hook implementations
- Error handling and logging throughout all components

### Security Guidelines

- Enhanced input validation documentation
- Improved session security guidelines
- Added CSRF protection recommendations
- Documented OAuth2/OpenID Connect security best practices
- Security considerations documented in all components

## [1.0.0] - 2024-01-15

### Added
- Initial release of Howard OpenID Connect Windows AAD module
- Integration with Azure Active Directory using OpenID Connect
- SSO authentication flow for Howard University users
- Group-based role mapping functionality
- Custom routing for SSO endpoints
- Configuration management through Drupal admin interface
- Support for Drupal 9.x and 10.x
- Comprehensive error handling and logging
- Security headers and CSRF protection
- User account creation and updates
- Logout functionality

### Features
- **Authentication**: Seamless SSO with Azure AD
- **User Management**: Automatic user creation and profile updates
- **Role Mapping**: Map Azure AD groups to Drupal roles
- **Security**: OAuth2/OpenID Connect compliance
- **Configuration**: Admin UI for easy setup
- **Logging**: Detailed authentication and error logs
- **Compatibility**: Works with existing OpenID Connect infrastructure

### Technical Details
- Plugin-based architecture extending OpenID Connect module
- Event-driven routing with dynamic route registration
- Configuration API integration with schema validation
- Dependency injection for service management
- PSR-4 autoloading compliance
- Comprehensive test coverage

### Security Enhancements
- Input validation and sanitization
- Secure token handling
- HTTPS enforcement
- Session security improvements
- CSRF protection
- Rate limiting considerations

### Dependencies
- Drupal core 9.x or 10.x
- OpenID Connect module (^1.4 || ^2.0 || ^3.0)
- PHP 8.1+
- Azure AD tenant with configured application

### Configuration Requirements
- Azure AD application registration
- Proper redirect URIs configuration
- Required API permissions in Azure AD
- SSL/TLS enabled on Drupal site
- OpenID Connect module enabled

### Known Issues
- None currently identified

### Breaking Changes
- None (initial release)

## Development Notes

### Release Process
1. Update version in `howard_openid_connect_windows_aad.info.yml`
2. Update this changelog with new features and fixes
3. Create git tag with version number
4. Push tag to repository
5. Create release notes
6. Update documentation if needed

### Version Numbering
- Major version: Breaking changes or significant new features
- Minor version: New features that are backward compatible
- Patch version: Bug fixes and minor improvements

### Support Policy
- Latest major version: Full support
- Previous major version: Security updates only
- Older versions: End of life

### Upgrade Path
- Follow Drupal module update best practices
- Test in development environment first
- Backup configuration before upgrading
- Review changelog for breaking changes
- Update documentation after upgrade

---

*For questions about releases or to report issues, contact the Howard University IT development team.*
