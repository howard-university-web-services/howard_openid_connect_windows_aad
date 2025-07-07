# Coding Standards

This document outlines the coding standards and best practices for the Howard OpenID Connect Windows AAD module development.

## Table of Contents

- [General Principles](#general-principles)
- [PHP Standards](#php-standards)
- [Drupal Standards](#drupal-standards)
- [Security Standards](#security-standards)
- [Documentation Standards](#documentation-standards)
- [Testing Standards](#testing-standards)
- [Code Review Guidelines](#code-review-guidelines)
- [Tools and Automation](#tools-and-automation)

## General Principles

### Code Quality
- Write clean, readable, and maintainable code
- Follow the DRY (Don't Repeat Yourself) principle
- Use meaningful variable and function names
- Keep functions and classes focused on single responsibilities
- Implement proper error handling and logging

### Consistency
- Follow established patterns throughout the codebase
- Use consistent naming conventions
- Maintain consistent code formatting
- Apply consistent documentation practices

### Performance
- Optimize database queries
- Minimize API calls to Azure AD
- Use appropriate caching strategies
- Profile critical code paths
- Consider memory usage implications

## PHP Standards

### PHP Version Support
- Target PHP 8.1+ features
- Maintain compatibility with supported Drupal versions
- Use modern PHP features appropriately
- Follow PSR standards where applicable

### Code Formatting
```php
<?php

declare(strict_types=1);

namespace Drupal\howard_openid_connect_windows_aad\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for handling Windows AAD SSO authentication.
 */
class WindowsAadSSOController extends ControllerBase {

  /**
   * Handles SSO authentication requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   */
  public function authenticate(Request $request) {
    // Implementation
  }

}
```

### Type Declarations
- Use strict type declarations: `declare(strict_types=1);`
- Add parameter and return type hints
- Use nullable types when appropriate
- Document complex types in PHPDoc

### Error Handling
```php
try {
  $result = $this->performOperation();
}
catch (SpecificException $e) {
  $this->logger->error('Operation failed: @message', [
    '@message' => $e->getMessage(),
  ]);
  throw new CustomException('Friendly error message', 0, $e);
}
```

## Drupal Standards

### Coding Standards Compliance
- Follow [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- Use PHP_CodeSniffer with Drupal standards
- Run `phpcs --standard=Drupal,DrupalPractice`
- Fix all coding standards violations

### File Organization
```
src/
├── Controller/           # Controllers for handling HTTP requests
├── Plugin/              # Plugin implementations
│   └── OpenIDConnectClient/
├── Routing/             # Route subscribers and providers
├── Service/             # Custom services
└── EventSubscriber/     # Event subscribers
```

### Naming Conventions
- Classes: `PascalCase` (e.g., `WindowsAadSSOController`)
- Methods: `camelCase` (e.g., `authenticateUser`)
- Variables: `snake_case` (e.g., `$user_account`)
- Constants: `UPPER_SNAKE_CASE` (e.g., `DEFAULT_TIMEOUT`)
- Files: `kebab-case.php` (e.g., `windows-aad-sso-controller.php`)

### Plugin Development
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
  // Plugin implementation
}
```

### Configuration Management
- Use configuration API for all settings
- Provide schema validation
- Include default configurations
- Document configuration options

### Service Dependencies
```php
/**
 * Constructs a new WindowsAadSSOController.
 *
 * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
 *   The configuration factory.
 * @param \Psr\Log\LoggerInterface $logger
 *   The logger service.
 */
public function __construct(
  ConfigFactoryInterface $config_factory,
  LoggerInterface $logger
) {
  $this->configFactory = $config_factory;
  $this->logger = $logger;
}
```

## Security Standards

### Input Validation
- Validate all user inputs
- Sanitize data before processing
- Use Drupal's render system for output
- Implement CSRF protection

### Authentication Security
```php
// Validate tokens securely
if (!$this->tokenValidator->validateToken($token)) {
  throw new AccessDeniedException('Invalid authentication token');
}

// Secure session handling
$session = $request->getSession();
$session->migrate(TRUE);
```

### Data Protection
- Never log sensitive information
- Use HTTPS for all communications
- Implement proper session security
- Follow OAuth2/OpenID Connect security guidelines

### Access Control
```php
/**
 * Checks access for SSO authentication.
 *
 * @return \Drupal\Core\Access\AccessResultInterface
 *   The access result.
 */
public function access() {
  return AccessResult::allowedIf($this->isValidSSORequest())
    ->addCacheContexts(['url.query_args']);
}
```

## Documentation Standards

### File Headers
```php
<?php

declare(strict_types=1);

namespace Drupal\howard_openid_connect_windows_aad\Controller;

/**
 * @file
 * Contains \Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController.
 */
```

### Class Documentation
```php
/**
 * Controller for handling Windows AAD SSO authentication.
 *
 * This controller provides endpoints for Azure Active Directory integration
 * using the OpenID Connect protocol. It handles authentication flows,
 * user creation, and role mapping based on Azure AD groups.
 *
 * @see https://docs.microsoft.com/en-us/azure/active-directory/
 */
class WindowsAadSSOController extends ControllerBase {
```

### Method Documentation
```php
/**
 * Authenticates user via Azure AD SSO.
 *
 * This method handles the OAuth2 authorization code flow for Azure AD
 * authentication. It validates the incoming request, exchanges the
 * authorization code for tokens, and creates or updates the user account.
 *
 * @param \Symfony\Component\HttpFoundation\Request $request
 *   The HTTP request containing authorization code and state parameters.
 *
 * @return \Symfony\Component\HttpFoundation\RedirectResponse
 *   Redirect response to the appropriate destination after authentication.
 *
 * @throws \Drupal\Core\Access\AccessException
 *   When the authentication request is invalid or unauthorized.
 * @throws \RuntimeException
 *   When Azure AD communication fails or returns invalid data.
 */
public function authenticate(Request $request) {
```

### Inline Comments
```php
// Validate the state parameter to prevent CSRF attacks
if (!$this->validateState($request->get('state'))) {
  throw new AccessException('Invalid state parameter');
}

// Exchange authorization code for access token
$token_response = $this->exchangeCodeForToken($request->get('code'));

// TODO: Implement token refresh mechanism for long-lived sessions
// @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow#refresh-the-access-token
```

## Testing Standards

### Unit Tests
```php
/**
 * Tests for the Windows AAD SSO controller.
 *
 * @group howard_openid_connect_windows_aad
 * @coversDefaultClass \Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController
 */
class WindowsAadSSOControllerTest extends UnitTestCase {

  /**
   * Tests the authenticate method with valid parameters.
   *
   * @covers ::authenticate
   */
  public function testAuthenticateWithValidParameters() {
    // Test implementation
  }
}
```

### Test Coverage
- Aim for 80%+ code coverage
- Test both success and failure scenarios
- Include edge cases and error conditions
- Mock external dependencies (Azure AD)

### Integration Tests
```php
/**
 * Tests Windows AAD integration.
 *
 * @group howard_openid_connect_windows_aad_integration
 */
class WindowsAadIntegrationTest extends BrowserTestBase {
  
  /**
   * Tests the complete SSO authentication flow.
   */
  public function testSSOAuthenticationFlow() {
    // Integration test implementation
  }
}
```

## Code Review Guidelines

### Review Checklist
- [ ] Code follows Drupal coding standards
- [ ] Security best practices are implemented
- [ ] Documentation is complete and accurate
- [ ] Tests are included and pass
- [ ] Performance implications are considered
- [ ] Error handling is appropriate
- [ ] Configuration changes are documented

### Review Process
1. **Self-review**: Review your own code before submitting
2. **Peer review**: At least one other developer must review
3. **Security review**: For authentication/security changes
4. **Testing**: All tests must pass
5. **Documentation**: Update relevant documentation

### Common Issues
- Missing or incomplete documentation
- Inadequate error handling
- Security vulnerabilities
- Performance problems
- Coding standards violations
- Missing test coverage

## Tools and Automation

### Code Quality Tools
```bash
# PHP_CodeSniffer for coding standards
./vendor/bin/phpcs --standard=Drupal,DrupalPractice src/

# PHP Code Beautifier and Fixer
./vendor/bin/phpcbf --standard=Drupal src/

# PHPStan for static analysis
./vendor/bin/phpstan analyse src/

# PHPMD for mess detection
./vendor/bin/phpmd src/ text cleancode,codesize,controversial,design,naming,unusedcode
```

### Pre-commit Hooks
```bash
#!/bin/sh
# Pre-commit hook to run coding standards checks

# Run PHPCS
./vendor/bin/phpcs --standard=Drupal,DrupalPractice src/
if [ $? -ne 0 ]; then
    echo "Coding standards violations found. Please fix before committing."
    exit 1
fi

# Run PHPStan
./vendor/bin/phpstan analyse src/
if [ $? -ne 0 ]; then
    echo "Static analysis errors found. Please fix before committing."
    exit 1
fi
```

### IDE Configuration
- Configure your IDE to follow Drupal coding standards
- Set up automatic code formatting
- Enable syntax highlighting for YAML and Twig
- Install relevant plugins for Drupal development

### Continuous Integration
- Run automated tests on all commits
- Check coding standards compliance
- Perform security scans
- Generate code coverage reports
- Deploy to staging environment for testing

## Enforcement

### Automated Checks
- All code must pass automated coding standards checks
- Security scans must pass without critical issues
- Test suite must pass with 80%+ coverage
- Documentation must be complete and accurate

### Manual Reviews
- Peer code reviews are mandatory
- Security reviews for authentication code
- Performance reviews for critical paths
- Architecture reviews for significant changes

### Exceptions
- Document any exceptions to these standards
- Provide justification for exceptions
- Get approval from lead developer
- Plan remediation where possible

---

*These standards are living documents and may be updated as the project evolves. For questions or suggestions, contact the Howard University IT development team.*
