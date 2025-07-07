# Developer Guide

This guide provides technical details for developers working on or extending the Howard OpenID Connect Windows AAD module.

## Table of Contents

- [Development Environment Setup](#development-environment-setup)
- [Module Architecture](#module-architecture)
- [Code Organization](#code-organization)
- [Testing](#testing)
- [Debugging](#debugging)
- [Contributing](#contributing)
- [Code Standards](#code-standards)

## Development Environment Setup

### Prerequisites

- Drupal 9.x or 10.x development environment
- PHP 8.1+ with required extensions
- Composer for dependency management
- Access to Azure AD tenant for testing
- Git for version control

### Installation for Development

1. **Clone the repository:**
   ```bash
   git clone [repository-url]
   cd howard_openid_connect_windows_aad
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Enable the module:**
   ```bash
   drush en howard_openid_connect_windows_aad
   ```

4. **Configure test Azure AD application** (see [INSTALL.md](INSTALL.md))

## Module Architecture

### Core Components

```
src/
├── Controller/
│   └── WindowsAadSSOController.php    # SSO endpoint controller
├── Plugin/
│   └── OpenIDConnectClient/
│       └── HowardWindowsAad.php       # OpenID Connect client plugin
└── Routing/
    └── HowardWindowsAadSSORouteSubscriber.php  # Dynamic route subscriber
```

### Configuration Schema

```
config/
├── install/
│   └── openid_connect.settings.windows_aad.yml  # Default settings
└── schema/
    └── openid_connect_windows_aad.schema.yml     # Configuration validation
```

### Key Design Patterns

1. **Plugin Architecture**: Extends OpenID Connect module's plugin system
2. **Event-Driven**: Uses Drupal's event subscriber pattern for routing
3. **Configuration Management**: Leverages Drupal's configuration API
4. **Dependency Injection**: Uses Drupal's service container

## Code Organization

### Plugin Development

The `HowardWindowsAad` plugin extends `OpenIDConnectClientBase`:

```php
/**
 * Howard University Azure AD OpenID Connect client.
 *
 * @OpenIDConnectClient(
 *   id = "howard_windows_aad",
 *   label = @Translation("Howard University Azure AD")
 * )
 */
class HowardWindowsAad extends OpenIDConnectClientBase {
  // Implementation details
}
```

### Controller Pattern

The SSO controller handles authentication endpoints:

```php
class WindowsAadSSOController extends ControllerBase {
  
  /**
   * Handles SSO authentication requests.
   */
  public function authenticate(Request $request) {
    // Authentication logic
  }
}
```

### Route Subscriber

Dynamic route registration based on configuration:

```php
class HowardWindowsAadSSORouteSubscriber extends RouteSubscriberBase {
  
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Route modification logic
  }
}
```

## Testing

### Unit Testing

Create unit tests for core functionality:

```php
/**
 * Tests for HowardWindowsAad plugin.
 *
 * @group howard_openid_connect_windows_aad
 */
class HowardWindowsAadTest extends UnitTestCase {
  
  /**
   * Test plugin configuration.
   */
  public function testPluginConfiguration() {
    // Test implementation
  }
}
```

### Integration Testing

Test with actual Azure AD integration:

1. **Set up test Azure AD application**
2. **Configure test environment variables**
3. **Run authentication flow tests**

### Manual Testing Checklist

- [ ] SSO login flow
- [ ] Group mapping functionality
- [ ] User creation and updates
- [ ] Error handling
- [ ] Configuration validation
- [ ] Security headers
- [ ] Logout functionality

## Debugging

### Enable Debug Logging

Add to `settings.php`:

```php
$config['system.logging']['error_level'] = 'verbose';
```

### Common Debug Points

1. **Authentication Flow:**
   ```php
   \Drupal::logger('howard_openid_connect_windows_aad')
     ->debug('Authentication attempt: @data', ['@data' => $data]);
   ```

2. **Group Mapping:**
   ```php
   \Drupal::logger('howard_openid_connect_windows_aad')
     ->debug('Group mapping: @groups', ['@groups' => print_r($groups, TRUE)]);
   ```

3. **Configuration Issues:**
   ```php
   $config = $this->configFactory->get('openid_connect.settings.windows_aad');
   \Drupal::logger('howard_openid_connect_windows_aad')
     ->debug('Configuration: @config', ['@config' => print_r($config->getRawData(), TRUE)]);
   ```

### Troubleshooting Tools

- **Drush commands** for configuration management
- **Database queries** for user/role inspection
- **Network inspection** for Azure AD communication
- **Log analysis** for error tracking

## Contributing

### Development Workflow

1. **Create feature branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make changes following coding standards**

3. **Add/update tests**

4. **Update documentation**

5. **Submit pull request**

### Code Review Checklist

- [ ] Follows Drupal coding standards
- [ ] Includes appropriate documentation
- [ ] Has test coverage
- [ ] Handles errors gracefully
- [ ] Follows security best practices
- [ ] Updates changelog

### Release Process

1. **Update version in `*.info.yml`**
2. **Update `CHANGELOG.md`**
3. **Tag release:**
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

## Code Standards

### PHP Standards

- Follow [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- Use PHP 8.1+ features appropriately
- Implement proper error handling
- Include comprehensive documentation

### Security Considerations

- Validate all user inputs
- Use CSRF protection
- Implement proper session management
- Follow OAuth2/OpenID Connect security guidelines
- Regular security reviews

### Performance Guidelines

- Minimize database queries
- Use caching appropriately
- Optimize Azure AD API calls
- Monitor memory usage
- Profile critical paths

## Extension Points

### Custom Group Mapping

Extend group mapping functionality:

```php
/**
 * Implements hook_howard_openid_connect_windows_aad_group_mapping_alter().
 */
function mymodule_howard_openid_connect_windows_aad_group_mapping_alter(&$roles, $groups, $account) {
  // Custom group mapping logic
}
```

### Custom User Processing

Modify user data processing:

```php
/**
 * Implements hook_howard_openid_connect_windows_aad_user_presave().
 */
function mymodule_howard_openid_connect_windows_aad_user_presave($account, $context) {
  // Custom user processing
}
```

### Event Subscribers

Create custom event subscribers:

```php
class CustomSSOEventSubscriber implements EventSubscriberInterface {
  
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'howard_openid_connect_windows_aad.sso_success' => 'onSSOSuccess',
    ];
  }
}
```

## Resources

- [Drupal API Documentation](https://api.drupal.org)
- [OpenID Connect Specification](https://openid.net/connect/)
- [Azure AD Documentation](https://docs.microsoft.com/en-us/azure/active-directory/)
- [Howard University IT Standards](../CODING_STANDARDS.md)

---

*For additional support, contact the Howard University IT development team.*
