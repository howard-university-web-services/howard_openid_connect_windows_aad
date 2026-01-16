# API Documentation - Howard OpenID Connect Windows Azure Active Directory

This document provides comprehensive API documentation for the Howard OpenID Connect Windows Azure Active Directory module, including technical specifications, plugin architecture, and integration details.

## Overview

The Howard OpenID Connect Windows Azure Active Directory module extends Drupal's OpenID Connect framework to provide seamless authentication with Microsoft Azure Active Directory. It implements a custom OpenID Connect client plugin specifically designed for Howard University's infrastructure and security requirements.

## Architecture

### Module Structure

```
howard_openid_connect_windows_aad/
├── src/
│   ├── Plugin/
│   │   └── OpenIDConnectClient/
│   │       └── HowardWindowsAad.php        # Main client plugin
│   ├── Controller/
│   │   └── WindowsAadSSOController.php     # Single Sign-Out controller
│   └── Routing/
│       └── HowardWindowsAadSSORouteSubscriber.php # Route subscriber
├── config/
│   ├── install/
│   │   └── openid_connect.settings.windows_aad.yml
│   └── schema/
│       └── openid_connect_windows_aad.schema.yml
├── howard_openid_connect_windows_aad.routing.yml
├── howard_openid_connect_windows_aad.services.yml
└── howard_openid_connect_windows_aad.info.yml
```

### Core Components

#### 1. OpenID Connect Client Plugin
- **Purpose**: Handles Azure AD authentication flow
- **Location**: `src/Plugin/OpenIDConnectClient/HowardWindowsAad.php`
- **Interface**: Implements `OpenIDConnectClientInterface`

#### 2. Single Sign-Out Controller
- **Purpose**: Manages SSO logout functionality
- **Location**: `src/Controller/WindowsAadSSOController.php`
- **Responsibilities**: Handles logout requests from Azure AD

#### 3. Route Subscriber
- **Purpose**: Defines custom routing for SSO operations
- **Location**: `src/Routing/HowardWindowsAadSSORouteSubscriber.php`
- **Function**: Registers SSO-specific routes

## OpenID Connect Client Plugin API

### HowardWindowsAad Class

The main plugin class that extends `OpenIDConnectClientBase`.

**Class Declaration**:
```php
/**
 * Howard Windows Azure AD OpenID Connect client.
 *
 * @OpenIDConnectClient(
 *   id = "windows_aad",
 *   label = @Translation("Howard Windows Azure AD")
 * )
 */
class HowardWindowsAad extends OpenIDConnectClientBase
```

### Configuration Methods

#### buildConfigurationForm()

Builds the configuration form for the Azure AD client.

**Method Signature**:
```php
public function buildConfigurationForm(array $form, FormStateInterface $form_state): array
```

**Parameters**:
- `$form`: Base form array from parent class
- `$form_state`: Current form state object

**Returns**: Enhanced form array with Azure AD specific fields

**Configuration Fields**:
- `enable_single_sign_out`: Boolean checkbox for SSO logout
- `authorization_endpoint_wa`: Azure AD authorization endpoint URL
- `token_endpoint_wa`: Azure AD token endpoint URL
- `map_ad_groups_to_roles`: Boolean for group-to-role mapping
- `group_mapping[mappings]`: Textarea for manual group mappings

**Field Structure**:
```php
$form['enable_single_sign_out'] = [
  '#title' => $this->t('Enable Single Sign Out'),
  '#type' => 'checkbox',
  '#default_value' => $this->configuration['enable_single_sign_out'] ?? FALSE,
  '#description' => $this->t('Enable Single Sign Out functionality...'),
];

$form['group_mapping']['mappings'] = [
  '#title' => $this->t('Manual mappings'),
  '#type' => 'textarea',
  '#default_value' => $this->configuration['group_mapping']['mappings'] ?? '',
  '#description' => $this->t('Format: role|group1;group2'),
];
```

### Endpoint Configuration

#### getEndpoints()

Returns the Azure AD OAuth2 endpoints for authentication.

**Method Signature**:
```php
public function getEndpoints(): array
```

**Returns**:
```php
[
  'authorization' => 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize',
  'token' => 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token',
]
```

**Endpoint URLs**:
- **Authorization**: Where users are redirected for login
- **Token**: Where authorization codes are exchanged for tokens

### Token Management

#### retrieveIdToken()

Exchanges authorization code for ID token from Azure AD.

**Method Signature**:
```php
public function retrieveIdToken(string $authorization_code): array|bool
```

**Parameters**:
- `$authorization_code`: OAuth2 authorization code from Azure AD

**Returns**:
- `array`: Token response data on success
- `bool`: FALSE on failure

**Token Exchange Process**:
1. Prepare token request with authorization code
2. Add client credentials and redirect URI
3. Send POST request to token endpoint
4. Validate response and extract tokens
5. Return token data or FALSE on error

**Response Structure**:
```php
[
  'id_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
  'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
  'refresh_token' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
  'expires_in' => 3600,
  'token_type' => 'Bearer',
]
```

### User Information

#### retrieveUserInfo()

Fetches user information from Azure AD using access token.

**Method Signature**:
```php
public function retrieveUserInfo(string $access_token): array|bool
```

**Parameters**:
- `$access_token`: Valid Azure AD access token

**Returns**:
- `array`: User information from Microsoft Graph API
- `bool`: FALSE on failure

**User Info Structure**:
```php
[
  'sub' => 'user-object-id',
  'email' => 'user@howard.edu',
  'preferred_username' => 'username@howard.edu',
  'given_name' => 'First',
  'family_name' => 'Last',
  'name' => 'First Last',
  'groups' => ['group-id-1', 'group-id-2'], // if requested
]
```

### Authorization URL Generation

#### getAuthorizationUri()

Generates the Azure AD authorization URL for user redirection.

**Method Signature**:
```php
public function getAuthorizationUri(array $scopes = ['openid', 'email', 'profile']): string
```

**Parameters**:
- `$scopes`: Array of OAuth2 scopes to request

**Returns**: Complete authorization URL with parameters

**URL Parameters**:
- `client_id`: Application ID from Azure AD
- `response_type`: Set to 'code' for authorization code flow
- `redirect_uri`: Callback URL for the application
- `scope`: Space-separated scope values
- `state`: CSRF protection token
- `nonce`: Replay attack protection

**Example URL**:
```
https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize?
client_id={client_id}&
response_type=code&
redirect_uri={redirect_uri}&
scope=openid%20email%20profile&
state={state}&
nonce={nonce}
```

## Group Mapping API

### processGroupMapping()

Maps Azure AD groups to Drupal roles based on configuration.

**Method Signature**:
```php
protected function processGroupMapping(array $user_info, UserInterface $account): void
```

**Parameters**:
- `$user_info`: User information from Azure AD including groups
- `$account`: Drupal user account object

**Process**:
1. Extract groups from user info
2. Parse group mapping configuration
3. Determine roles based on group membership
4. Update user account with new roles
5. Log role assignments for audit

**Mapping Format**:
```
role_name|group_id_or_name
administrator|IT Admins;Web Team
editor|Content Editors
faculty|Faculty Group
```

**Group Resolution**:
- Supports both Azure AD Object IDs and Display Names
- Object IDs are preferred for stability
- Case-sensitive matching for group names

## Single Sign-Out API

### WindowsAadSSOController

Handles Single Sign-Out requests from Azure AD.

#### Class Declaration

```php
/**
 * Controller for Azure AD single sign out user routes.
 */
class WindowsAadSSOController extends ControllerBase
```

#### Dependencies

```php
/**
 * @param LoggerInterface $logger
 *   Logger service for audit logging
 * @param OpenIDConnectAuthmap $authmap
 *   OpenID Connect user mapping service
 */
public function __construct(LoggerInterface $logger, OpenIDConnectAuthmap $authmap)
```

### SSO Logout Methods

#### singleSignOut()

Handles logout requests initiated from Azure AD.

**Method Signature**:
```php
public function singleSignOut(Request $request): Response
```

**Parameters**:
- `$request`: HTTP request object containing logout parameters

**Returns**: HTTP response object

**Process**:
1. Validate logout request from Azure AD
2. Extract user session information
3. Log out user from Drupal session
4. Clear authentication mappings
5. Return appropriate HTTP response
6. Log logout event for audit

**Request Validation**:
- Verify request origin from Azure AD
- Validate required parameters
- Check user session validity
- Ensure CSRF protection

#### logoutRedirect()

Handles post-logout redirection after SSO logout.

**Method Signature**:
```php
public function logoutRedirect(): TrustedRedirectResponse
```

**Returns**: Redirect response to appropriate logout page

**Redirect Logic**:
1. Check for configured logout destination
2. Default to site homepage
3. Add logout confirmation message
4. Clear any remaining session data

## Route Subscriber API

### HowardWindowsAadSSORouteSubscriber

Defines custom routes for SSO functionality.

**Class Declaration**:
```php
/**
 * Route subscriber for Howard Windows AAD SSO routes.
 */
class HowardWindowsAadSSORouteSubscriber implements EventSubscriberInterface
```

### Route Definitions

#### alterRoutes()

Modifies existing routes or adds new SSO routes.

**Method Signature**:
```php
protected function alterRoutes(RouteCollection $collection): void
```

**Custom Routes**:
- `/openid-connect/windows_aad/signout`: SSO logout endpoint
- `/openid-connect/windows_aad/logout-redirect`: Post-logout redirect

**Route Configuration**:
```yaml
howard_openid_connect_windows_aad.sso_signout:
  path: '/openid-connect/windows_aad/signout'
  defaults:
    _controller: '\Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController::singleSignOut'
  requirements:
    _access: 'TRUE'
  methods: [GET, POST]
```

## Configuration Schema

### Schema Definition

The module defines configuration schema for proper validation.

**Schema Structure**:
```yaml
openid_connect.settings.windows_aad:
  type: openid_connect_client.base
  label: 'Howard Windows Azure AD settings'
  mapping:
    enable_single_sign_out:
      type: boolean
      label: 'Enable Single Sign Out'
    authorization_endpoint_wa:
      type: string
      label: 'Authorization endpoint'
    token_endpoint_wa:
      type: string
      label: 'Token endpoint'
    map_ad_groups_to_roles:
      type: boolean
      label: 'Map AD groups to roles'
    group_mapping:
      type: mapping
      mapping:
        mappings:
          type: string
          label: 'Manual mappings'
```

### Default Configuration

**Installation Defaults**:
```yaml
settings:
  enable_single_sign_out: false
  authorization_endpoint_wa: 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize'
  token_endpoint_wa: 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token'
  map_ad_groups_to_roles: false
  group_mapping:
    mappings: ''
```

## Service Integration

### Service Dependencies

The module integices with several Drupal core and contrib services:

**Core Services**:
- `logger.factory`: For audit and error logging
- `current_user`: For user session management
- `entity_type.manager`: For user account operations
- `config.factory`: For configuration management

**OpenID Connect Services**:
- `openid_connect.authmap`: For user authentication mapping
- `openid_connect.session`: For session management

### Service Definition

```yaml
# howard_openid_connect_windows_aad.services.yml
services:
  howard_openid_connect_windows_aad.route_subscriber:
    class: Drupal\howard_openid_connect_windows_aad\Routing\HowardWindowsAadSSORouteSubscriber
    tags:
      - { name: event_subscriber }
```

## Security Considerations

### Token Security

**Token Storage**:
- ID tokens are validated and parsed, not stored
- Access tokens used only for immediate API calls
- Refresh tokens handled by OpenID Connect base module
- All tokens transmitted over HTTPS only

**Token Validation**:
- JWT signature verification against Azure AD public keys
- Audience claim validation to prevent token reuse
- Expiration time checking for all tokens
- Nonce validation for replay attack prevention

### CSRF Protection

**State Parameter**:
- Unique state parameter generated for each auth request
- State validated on callback to prevent CSRF attacks
- State includes session-specific information

**Nonce Parameter**:
- Unique nonce included in authentication requests
- Nonce validated in ID token claims
- Prevents replay attacks

### Input Validation

**Authorization Code**:
- Format validation of authorization codes
- Length and character set validation
- Expiration time checking

**User Information**:
- Sanitization of all user data from Azure AD
- Validation of email format and domain
- Group membership validation

### Audit Logging

**Authentication Events**:
- Successful login attempts
- Failed authentication attempts
- Token exchange operations
- Group mapping changes

**SSO Events**:
- Single sign-out requests
- Logout redirect operations
- Session termination events

**Log Format**:
```php
$this->logger->info('Azure AD authentication successful for user %user from IP %ip', [
  '%user' => $user_info['email'],
  '%ip' => $request->getClientIp(),
]);
```

## Extension Points

### Custom Hooks

The module provides several extension points for customization:

#### hook_howard_openid_connect_windows_aad_user_presave()

Called before saving user account after Azure AD authentication.

**Usage**:
```php
/**
 * Implements hook_howard_openid_connect_windows_aad_user_presave().
 */
function mymodule_howard_openid_connect_windows_aad_user_presave($account, $user_info) {
  // Custom user data processing
  $account->set('field_department', $user_info['department']);
}
```

#### hook_howard_openid_connect_windows_aad_group_mapping_alter()

Allows modification of group-to-role mappings.

**Usage**:
```php
/**
 * Implements hook_howard_openid_connect_windows_aad_group_mapping_alter().
 */
function mymodule_howard_openid_connect_windows_aad_group_mapping_alter(&$mappings, $user_info) {
  // Dynamic role assignment based on user attributes
  if ($user_info['department'] === 'IT') {
    $mappings['administrator'] = TRUE;
  }
}
```

### Plugin Alteration

#### OpenID Connect Client Plugin Alteration

Extend or modify the client plugin behavior:

```php
/**
 * Custom Azure AD client extending Howard implementation.
 */
class CustomHowardWindowsAad extends HowardWindowsAad {
  
  /**
   * Custom user info retrieval with additional claims.
   */
  public function retrieveUserInfo($access_token) {
    $user_info = parent::retrieveUserInfo($access_token);
    
    // Add custom user information processing
    $user_info['custom_field'] = $this->getCustomUserData($access_token);
    
    return $user_info;
  }
}
```

## Testing API

### Unit Testing

**Test Base Class**:
```php
use Drupal\Tests\UnitTestCase;
use Drupal\howard_openid_connect_windows_aad\Plugin\OpenIDConnectClient\HowardWindowsAad;

class HowardWindowsAadTest extends UnitTestCase {
  
  /**
   * Test endpoint configuration.
   */
  public function testGetEndpoints() {
    $client = new HowardWindowsAad([], 'windows_aad', []);
    $endpoints = $client->getEndpoints();
    
    $this->assertArrayHasKey('authorization', $endpoints);
    $this->assertArrayHasKey('token', $endpoints);
  }
}
```

### Integration Testing

**Functional Test Example**:
```php
use Drupal\Tests\BrowserTestBase;

class AzureAdIntegrationTest extends BrowserTestBase {
  
  /**
   * Test Azure AD authentication flow.
   */
  public function testAzureAdAuthentication() {
    // Mock Azure AD responses
    $this->mockAzureAdEndpoints();
    
    // Test authentication flow
    $this->drupalGet('/openid-connect/windows_aad');
    $this->assertSession()->statusCodeEquals(302);
    
    // Verify redirect to Azure AD
    $this->assertSession()->responseHeaderContains('Location', 'login.microsoftonline.com');
  }
}
```

## Module Functions

### Group Fetching via Microsoft Graph API

#### _howard_openid_connect_windows_aad_fetch_user_groups()

Fetches user group memberships directly from Microsoft Graph API when groups are not available in the authentication context.

**Function Signature**:
```php
function _howard_openid_connect_windows_aad_fetch_user_groups(array $context, UserInterface $account): array
```

**Parameters**:
- `$context`: OpenID Connect authentication context containing tokens and user data
- `$account`: Drupal user account being processed

**Returns**:
- `array`: Array of group IDs and display names the user belongs to
- Empty array if tokens unavailable or API call fails

**Example Usage**:
```php
// Called automatically during user info save process
$groups = _howard_openid_connect_windows_aad_fetch_user_groups($context, $account);

// Example return value
[
  'group-uuid-1' => 'group-uuid-1',
  'Admin Group' => 'Admin Group',
  'group-uuid-2' => 'group-uuid-2',
  'Faculty' => 'Faculty',
]
```

**Graph API Integration**:
- Uses Microsoft Graph API endpoint: `https://graph.microsoft.com/v1.0/users/{user_id}/memberOf`
- Requires access token with appropriate scopes
- Gracefully handles missing tokens or API failures
- Includes comprehensive error logging for debugging

**Error Handling**:
The function implements robust error handling:
- Logs informational messages when tokens are unavailable
- Logs errors for API failures with status codes
- Logs exceptions with detailed error messages
- Returns empty array on any failure to allow normal authentication flow

**Security Considerations**:
- Validates access token availability before making API calls
- Uses secure HTTP client with proper headers
- Implements timeout protection (10-second limit)
- Follows principle of graceful degradation

### Hook Implementations

#### howard_openid_connect_windows_aad_openid_connect_userinfo_save()

Processes user information after successful Azure AD authentication, including automatic group fetching via Graph API when groups are missing from the authentication context.

**Enhanced Features in v11.0.10**:
- Automatic fallback to Microsoft Graph API for group retrieval
- Improved error handling and logging
- Better configuration path handling
- Enhanced user experience with automatic account activation

## Performance Considerations

### Caching Strategy

**Endpoint Caching**:
- Azure AD discovery endpoints cached for 1 hour
- JWT public key caching for signature validation
- User group membership cached per session

**Token Management**:
- Minimal token storage to reduce memory usage
- Immediate token validation and parsing
- Proper cleanup of expired tokens

### Optimization Techniques

**Lazy Loading**:
- User information loaded only when needed
- Group membership fetched on-demand
- Microsoft Graph API calls minimized

**Batch Operations**:
- Group membership checks batched when possible
- Role assignments processed in bulk
- Audit logging queued for batch processing

---

*This API documentation provides technical specifications for developers integrating with or extending the Howard OpenID Connect Windows Azure Active Directory module. For implementation examples and customization guides, refer to the Developer Guide.*
