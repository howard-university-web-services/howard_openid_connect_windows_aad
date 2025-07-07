# Installation Guide - Howard OpenID Connect Windows Azure Active Directory

This guide provides comprehensive instructions for installing and configuring the Howard OpenID Connect Windows Azure Active Directory module for seamless SSO integration with Microsoft Azure AD.

## Overview

The Howard OpenID Connect Windows Azure Active Directory module enables secure Single Sign-On (SSO) authentication between Howard University's Drupal sites and Microsoft Azure Active Directory. This module is a customized implementation specifically designed for Howard University's infrastructure and security requirements.

## Prerequisites

### System Requirements

- **Drupal**: 10.0+ or 11.0+
- **PHP**: 8.1 or higher with OpenSSL extension
- **HTTPS**: SSL/TLS certificate required for production
- **Domain**: Publicly accessible domain for Azure AD callbacks

### Required Modules

- **OpenID Connect**: Core OpenID Connect module
- **User**: Core User module (enabled by default)

### Recommended Modules

- **External Auth**: For advanced user mapping
- **Role Delegation**: For role management
- **Security Kit**: For additional security headers

### Azure Active Directory Requirements

- **Azure AD Tenant**: Access to Howard University's Azure AD tenant
- **Application Registration**: Ability to register applications in Azure AD
- **Admin Permissions**: Rights to configure app registrations and permissions
- **Group Information**: Access to AD groups if using role mapping

## Azure AD Configuration

### Step 1: Register Application in Azure AD

1. **Access Azure Portal**:
   - Navigate to [Azure Portal](https://portal.azure.com)
   - Sign in with Howard University admin credentials
   - Go to "Azure Active Directory" > "App registrations"

2. **Create New Registration**:
   ```
   Name: [Site Name] - Drupal SSO
   Supported account types: Accounts in this organizational directory only (Howard University only - Single tenant)
   Redirect URI: Web - https://yoursite.edu/openid-connect/windows_aad
   ```

3. **Note Application Details**:
   - **Application (client) ID**: Copy this value
   - **Directory (tenant) ID**: Copy this value
   - **Object ID**: Note for reference

### Step 2: Configure Application Settings

1. **Authentication Settings**:
   - Go to "Authentication" in your app registration
   - Add redirect URIs:
     ```
     https://yoursite.edu/openid-connect/windows_aad
     https://yoursite.edu/openid-connect/windows_aad/signout
     ```
   - Enable "ID tokens" under Implicit grant and hybrid flows
   - Configure logout URL: `https://yoursite.edu/user/logout`

2. **API Permissions**:
   - Go to "API permissions"
   - Add permissions:
     ```
     Microsoft Graph:
     - openid (delegated)
     - profile (delegated)
     - email (delegated)
     - User.Read (delegated)
     - Group.Read.All (application) - if using group mapping
     ```
   - Grant admin consent for all permissions

3. **Certificates & Secrets**:
   - Go to "Certificates & secrets"
   - Create new client secret:
     ```
     Description: Drupal SSO Client Secret
     Expires: 24 months (recommended)
     ```
   - **Copy the secret value immediately** - it won't be shown again

### Step 3: Configure Token Configuration (Optional)

1. **Token Configuration**:
   - Go to "Token configuration"
   - Add optional claims if needed:
     ```
     ID tokens:
     - groups (if using group mapping)
     - family_name
     - given_name
     ```

## Module Installation

### Method 1: Composer (Recommended)

Composer installation ensures proper dependency management and easier updates.

```bash
# Navigate to your Drupal root directory
cd /path/to/drupal

# Install the module
composer require howard/howard_openid_connect_windows_aad

# Enable the module and dependencies
drush en openid_connect howard_openid_connect_windows_aad

# Clear caches
drush cr
```

### Method 2: Manual Installation

If Composer is not available:

1. **Download Dependencies**:
   - Download OpenID Connect module from [Drupal.org](https://www.drupal.org/project/openid_connect)
   - Download Howard OpenID Connect Windows AAD from [GitHub](https://github.com/howard-university-web-services/howard_openid_connect_windows_aad)

2. **Install Modules**:
   ```bash
   # Extract to modules directory
   tar -xzf openid_connect-[version].tar.gz -C modules/contrib/
   tar -xzf howard_openid_connect_windows_aad-[version].tar.gz -C modules/contrib/

   # Enable modules
   drush en openid_connect howard_openid_connect_windows_aad

   # Clear caches
   drush cr
   ```

### Method 3: Development Installation

For development or customization:

```bash
# Clone the repository
git clone https://github.com/howard-university-web-services/howard_openid_connect_windows_aad.git

# Move to modules directory
mv howard_openid_connect_windows_aad modules/contrib/

# Install dependencies
composer require drupal/openid_connect

# Enable modules
drush en openid_connect howard_openid_connect_windows_aad
```

## OpenID Connect Configuration

### Step 1: Access Configuration

1. **Navigate to OpenID Connect Settings**:
   - Go to Admin » Configuration » Web Services » OpenID Connect
   - URL: `/admin/config/services/openid-connect`

2. **Enable Howard Windows Azure AD Client**:
   - Check "Enable" for "Howard Windows Azure AD"
   - Click "Save configuration"

### Step 2: Configure Client Settings

1. **Basic Settings**:
   ```
   Client ID: [Application ID from Azure AD]
   Client Secret: [Client Secret from Azure AD]
   ```

2. **Endpoints Configuration**:
   ```
   Authorization Endpoint: 
   https://login.microsoftonline.com/[TENANT_ID]/oauth2/v2.0/authorize

   Token Endpoint:
   https://login.microsoftonline.com/[TENANT_ID]/oauth2/v2.0/token
   ```

3. **Advanced Settings**:
   ```
   ☑ Enable Single Sign Out
   ☑ Map user's AD groups to Drupal roles (if desired)
   ```

### Step 3: User Claims Mapping

Configure how Azure AD user information maps to Drupal user fields:

```
Mapping Configuration:
- Username: preferred_username or email
- Email: email
- First Name: given_name
- Last Name: family_name
```

## AD Group to Role Mapping

### Enable Group Mapping

1. **Check "Map user's AD groups to Drupal roles"**
2. **Configure Manual Mappings**:

```
Role Name|Group ID or Display Name
editor|Content Editors;Web Team
administrator|IT Administrators
faculty|Faculty Group
student|Student Group
```

### Group Mapping Format

- **Format**: `role_name|group_name` (one per line)
- **Multiple Groups**: Use semicolon to separate: `role|group1;group2;group3`
- **Group Identification**: Use Group ID (preferred) or Display Name
- **Case Sensitivity**: Group names are case-sensitive

### Finding Group Information

**Via Azure Portal**:
1. Go to Azure AD » Groups
2. Find your group and note:
   - Object ID (recommended for mapping)
   - Display Name (alternative)

**Via PowerShell**:
```powershell
Connect-AzureAD
Get-AzureADGroup -SearchString "GroupName"
```

## Single Sign-Out Configuration

### Enable Single Sign-Out

1. **Module Configuration**:
   - Check "Enable Single Sign Out" in client settings
   - Save configuration

2. **Azure AD Configuration**:
   - Add logout URL in Azure AD app registration:
   ```
   Front-channel logout URL: https://yoursite.edu/openid-connect/windows_aad/signout
   ```

3. **Test SSO Logout**:
   - Log in via Azure AD
   - Log out from Drupal
   - Verify logout from other Microsoft services

## Security Configuration

### HTTPS Requirements

**Production Requirements**:
- Valid SSL/TLS certificate
- HTTPS enforcement
- Secure cookie settings

**Configuration**:
```php
// In settings.php
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = ['your-proxy-ip'];

// Force HTTPS
if (!drupal_is_cli() && !isset($_SERVER['HTTPS'])) {
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], TRUE, 301);
  exit();
}
```

### Security Headers

**Recommended Headers**:
```
Content-Security-Policy: default-src 'self'; connect-src 'self' *.microsoftonline.com *.windows.net
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
```

## Testing Installation

### Basic Authentication Test

1. **Enable Test User Creation**:
   - Go to OpenID Connect settings
   - Enable "Automatically connect existing users"
   - Save configuration

2. **Test Login Flow**:
   - Navigate to `/user/login`
   - Click "Log in with Howard Windows Azure AD"
   - Complete Azure AD authentication
   - Verify successful login to Drupal

3. **Verify User Creation**:
   - Check that user account was created
   - Verify user information mapping
   - Test role assignment (if configured)

### Group Mapping Test

1. **Create Test User with Groups**:
   - Ensure test user is member of mapped AD groups
   - Log in via Azure AD
   - Check assigned Drupal roles

2. **Verify Role Synchronization**:
   - User should have roles corresponding to AD groups
   - Roles should update on subsequent logins

### Single Sign-Out Test

1. **Test Logout from Drupal**:
   - Log in via Azure AD
   - Log out from Drupal
   - Verify logout from Microsoft services

2. **Test External Logout**:
   - Log in via Azure AD to Drupal
   - Log out from Office 365 or other Microsoft service
   - Verify automatic logout from Drupal

## Post-Installation Configuration

### User Registration Settings

**Disable Local Registration** (recommended):
```
Admin » Configuration » People » Account settings
Who can register accounts: Administrators only
```

**Hide Password Fields**:
The module automatically hides password fields for Azure AD users.

### Menu and Block Configuration

**Add Login Block**:
1. Place "User login" block in appropriate region
2. Configure visibility settings
3. Azure AD login option will appear automatically

### Permissions Configuration

**Recommended Permissions**:
```
Anonymous User:
- Use OpenID Connect

Authenticated User:
- View own user account
- Edit own user account (limited fields)

Administrator:
- Administer OpenID Connect
- View user accounts
- Edit user accounts
```

## Troubleshooting

### Common Issues

#### Issue: "Invalid redirect URI" Error

**Symptoms**: Error during Azure AD authentication about redirect URI

**Solutions**:
1. **Verify Redirect URI in Azure AD**:
   - Must exactly match: `https://yoursite.edu/openid-connect/windows_aad`
   - Check for trailing slashes
   - Verify HTTPS vs HTTP

2. **Check Drupal Base URL**:
   ```php
   // In settings.php
   $base_url = 'https://yoursite.edu';
   ```

#### Issue: "Invalid client" Error

**Symptoms**: Authentication fails with client validation error

**Solutions**:
1. **Verify Client Credentials**:
   - Check Client ID matches Azure AD Application ID
   - Verify Client Secret is current and correct
   - Ensure no extra spaces in configuration

2. **Check Token Endpoint**:
   - Verify tenant ID in endpoint URL
   - Ensure using v2.0 endpoint format

#### Issue: Groups Not Mapping to Roles

**Symptoms**: Users authenticate but don't receive expected roles

**Solutions**:
1. **Verify Group Permissions**:
   - Ensure app has Group.Read.All permission
   - Verify admin consent was granted
   - Check user is actually member of mapped groups

2. **Check Mapping Configuration**:
   - Verify role names exist in Drupal
   - Check group IDs/names are correct
   - Ensure proper mapping format

3. **Enable Group Claims**:
   - Add "groups" claim to ID token in Azure AD
   - Or use Microsoft Graph API for group information

#### Issue: Single Sign-Out Not Working

**Symptoms**: Logout from one service doesn't log out from others

**Solutions**:
1. **Verify Logout URL Configuration**:
   - Check Front-channel logout URL in Azure AD
   - Ensure Single Sign-Out is enabled in module
   - Verify logout URL is accessible

2. **Check Session Configuration**:
   ```php
   // In settings.php
   ini_set('session.cookie_lifetime', 0);
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_httponly', 1);
   ```

### Debug Mode

**Enable Debugging**:
```php
// In settings.php
$config['system.logging']['error_level'] = 'verbose';

// Enable OpenID Connect debugging
$config['openid_connect.settings']['debug'] = TRUE;
```

**Check Logs**:
```bash
# View recent logs
drush watchdog:show --type=howard_openid_connect_windows_aad

# Monitor logs in real-time
tail -f sites/default/files/logs/drupal.log
```

### Performance Optimization

**Caching Configuration**:
```php
// In settings.php
$settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';
$settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
```

**Session Optimization**:
```php
// In settings.php
$settings['session_write_interval'] = 180;
$config['system.performance']['cache']['page']['max_age'] = 900;
```

## Updating

### Composer Updates

```bash
# Update to latest version
composer update howard/howard_openid_connect_windows_aad

# Update dependencies
composer update drupal/openid_connect

# Run database updates
drush updb

# Clear caches
drush cr
```

### Manual Updates

1. **Backup Current Installation**:
   ```bash
   # Backup database
   drush sql:dump > backup.sql
   
   # Backup files
   tar -czf modules-backup.tar.gz modules/contrib/
   ```

2. **Update Module Files**:
   - Download new version
   - Replace module directory
   - Run `drush updb`
   - Clear caches

### Update Checklist

- [ ] Backup database and files
- [ ] Test in development environment
- [ ] Verify Azure AD configuration still valid
- [ ] Test authentication flow
- [ ] Verify group mapping functionality
- [ ] Test single sign-out
- [ ] Update documentation if needed

## Uninstallation

### Safe Removal

```bash
# Disable module
drush pmu howard_openid_connect_windows_aad

# Remove configuration
drush config:delete openid_connect.settings.windows_aad

# Remove via Composer
composer remove howard/howard_openid_connect_windows_aad
```

### Clean Removal

**Remove User Data** (if needed):
```sql
-- Remove OpenID Connect user mappings
DELETE FROM authmap WHERE provider = 'windows_aad';

-- Clean up user sessions (optional)
TRUNCATE TABLE sessions;
```

### Azure AD Cleanup

1. **Remove Application Registration** (if no longer needed):
   - Go to Azure AD » App registrations
   - Delete the application
   - Clean up any custom permissions

2. **Update Group Memberships** (if needed):
   - Review and update AD group memberships
   - Remove any Drupal-specific groups

---

*For additional support, visit the [project repository](https://github.com/howard-university-web-services/howard_openid_connect_windows_aad) or submit an issue for assistance.*
