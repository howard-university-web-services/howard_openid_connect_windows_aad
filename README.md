# Howard OpenID Connect Windows Azure Active Directory

[![License](https://img.shields.io/badge/license-GPL%202%2B-blue.svg)](LICENSE)
[![Drupal](https://img.shields.io/badge/drupal-9.x%20%7C%2010.x%20%7C%2011.x-blue.svg)](https://www.drupal.org)
[![PHP](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)

A professional Drupal module providing seamless integration between Drupal and Microsoft Azure Active Directory through OpenID Connect, specifically customized for Howard University's authentication requirements.

## Quick Start

1. **Install the module:**
   ```bash
   composer require howard/howard_openid_connect_windows_aad
   drush en howard_openid_connect_windows_aad
   ```

2. **Configure Azure AD:**
   - Set up your Azure AD application ([detailed guide](docs/INSTALL.md))
   - Configure redirect URIs and permissions

3. **Configure Drupal:**
   - Visit `/admin/config/services/openid-connect`
   - Select "Howard University Azure AD" as client
   - Enter your Azure AD credentials

4. **Test authentication:**
   - Visit `/openid-connect/howard_windows_aad`
   - Complete the SSO flow

## Features

### 🔐 Secure Authentication
- **Single Sign-On (SSO)** with Azure Active Directory
- **OAuth2/OpenID Connect** compliance
- **CSRF protection** and secure token handling
- **HTTPS enforcement** for all authentication flows

### 👥 User Management
- **Automatic user creation** from Azure AD profiles
- **Profile synchronization** with Azure AD data
- **Group-based role mapping** from Azure AD to Drupal roles
- **Microsoft Graph API integration** for enhanced group fetching
- **Automatic fallback** to Graph API when groups are missing from authentication
- **Customizable user field mapping**

### 🛠 Howard University Customizations
- **Specialized authentication flow** for Howard University
- **Custom group mapping logic** for Howard's organizational structure
- **Enhanced security measures** for educational environments
- **Integration with Howard's existing systems**

### 🎨 User Experience
- **Seamless login experience** with Azure AD credentials
- **Automatic redirects** to intended destinations
- **Clean user interfaces** with hidden local password fields
- **Mobile-responsive** authentication flows

### ⚙️ Administration
- **Comprehensive configuration options** through Drupal admin UI
- **Detailed logging** for authentication events and errors
- **Flexible role mapping** configuration
- **Easy troubleshooting** with built-in diagnostics

## Architecture

This module extends Drupal's OpenID Connect ecosystem with:

- **Plugin-based architecture** for easy extension
- **Event-driven processing** for customization hooks
- **Service-oriented design** with dependency injection
- **Configuration API integration** for settings management
- **PSR-4 compliant** autoloading and namespace organization

## Documentation

| Document | Description |
|----------|-------------|
| **[Installation Guide](docs/INSTALL.md)** | Complete setup instructions with Azure AD configuration |
| **[API Documentation](docs/API.md)** | Technical API reference and integration details |
| **[Developer Guide](docs/DEVELOPER.md)** | Development setup, testing, and contribution guidelines |
| **[Changelog](docs/CHANGELOG.md)** | Version history and release notes |
| **[Coding Standards](docs/CODING_STANDARDS.md)** | Code quality and style guidelines |
| **[Release Checklist](docs/RELEASE_CHECKLIST.md)** | Quality assurance and release procedures |

## Requirements

### System Requirements
- **Drupal:** 9.x, 10.x, or 11.x
- **PHP:** 8.1 or higher
- **Database:** MySQL 5.7+, PostgreSQL 10+, or MariaDB 10.3+
- **Web Server:** Apache 2.4+ or Nginx 1.12+

### Dependencies
- **[OpenID Connect](https://www.drupal.org/project/openid_connect)** (^1.4 || ^2.0 || ^3.0)
- **Azure AD tenant** with configured application
- **SSL/TLS certificate** (required for production)

### Recommended
- **[idfive Component Library](https://bitbucket.org/idfivellc/idfive-component-library)** for UI components
- **[idfive Component Library D8 Theme](https://bitbucket.org/idfivellc/idfive-component-library-d8-theme)** for styling

## Installation

### Via Composer (Recommended)

```bash
# Install the module
composer require howard/howard_openid_connect_windows_aad

# Enable the module
drush en howard_openid_connect_windows_aad

# Clear cache
drush cr
```

### Manual Installation

1. Download the latest release from the repository
2. Extract to `modules/contrib/howard_openid_connect_windows_aad`
3. Enable via Drupal admin interface or Drush

For detailed installation instructions, see the [Installation Guide](docs/INSTALL.md).

## Configuration

### Basic Setup

1. **Navigate to OpenID Connect settings:**
   ```
   Administration » Configuration » Web Services » OpenID Connect
   ```

2. **Select Howard University Azure AD client:**
   - Check "Howard University Azure AD"
   - Configure client credentials from Azure AD

3. **Configure role mapping:**
   - Map Azure AD groups to Drupal roles
   - Set default roles for new users

4. **Test the configuration:**
   - Use the built-in test functionality
   - Verify user creation and role assignment

### Advanced Configuration

- **Custom field mapping** for user profiles
- **Group-based access control** configuration
- **Logging and monitoring** setup
- **Performance optimization** settings

See the [Installation Guide](docs/INSTALL.md) for comprehensive configuration details.

## Security

This module implements enterprise-grade security measures:

- ✅ **OAuth2/OpenID Connect** standard compliance
- ✅ **HTTPS enforcement** for all authentication flows
- ✅ **CSRF protection** using state parameters
- ✅ **Token validation** and secure storage
- ✅ **Input sanitization** and output encoding
- ✅ **Session security** with proper handling
- ✅ **Regular security audits** and updates

For detailed security information, see the [Installation Guide](docs/INSTALL.md#security-considerations).

## Support

### Getting Help
- **Documentation:** Check the [docs](docs/) directory for comprehensive guides
- **Issues:** Report bugs and feature requests through the project repository
- **Community:** Join Howard University's developer community discussions

### Professional Support
For enterprise support and custom development:
- Contact Howard University IT Services
- Professional consulting available through approved vendors

## Contributing

We welcome contributions! Please see our [Developer Guide](docs/DEVELOPER.md) for:

- Development environment setup
- Coding standards and guidelines
- Testing requirements
- Submission process

### Quick Contribution Guide

1. Fork the repository
2. Create a feature branch
3. Make your changes following our coding standards
4. Add/update tests as needed
5. Update documentation
6. Submit a pull request

## Testing

```bash
# Run unit tests
./vendor/bin/phpunit

# Check coding standards
./vendor/bin/phpcs --standard=Drupal,DrupalPractice src/

# Static analysis
./vendor/bin/phpstan analyse src/
```

See the [Developer Guide](docs/DEVELOPER.md#testing) for comprehensive testing information.

## Changelog

See [CHANGELOG.md](docs/CHANGELOG.md) for detailed version history and release notes.

## License

This project is licensed under the GNU General Public License v2.0 or later - see the [LICENSE](LICENSE) file for details.

## Credits

### Development Team
- **Howard University IT Services** - Primary development and maintenance
- **idfive** - Component library and theming support

### Based On
- **[OpenID Connect Microsoft Azure Active Directory client](https://www.drupal.org/project/openid_connect_windows_aad)** - Original foundation module
- **[OpenID Connect](https://www.drupal.org/project/openid_connect)** - Core OpenID Connect functionality

### Special Thanks
- Drupal community for the robust OpenID Connect ecosystem
- Microsoft for comprehensive Azure AD documentation
- All contributors and beta testers

---

**Howard University | Information Technology Services**  
*Empowering education through innovative technology solutions*
