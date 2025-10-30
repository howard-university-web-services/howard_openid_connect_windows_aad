# Release Notes v11.0.8

**Release Date:** October 30, 2025  
**Version:** 11.0.8  
**Type:** Minor Enhancement Release

## Overview

This release focuses on improving Microsoft Graph API integration, enhancing OAuth2 scope management, and providing better diagnostic capabilities for Azure AD authentication. Key improvements include explicit scope definitions, enhanced error logging, and more robust handling of Azure AD extension attributes.

## 🆕 New Features

### OAuth2 Scope Management
- **New `getScopes()` method**: Explicitly defines required OAuth2 scopes for Microsoft Graph API
- **Comprehensive scope coverage**: Includes `openid`, `profile`, `email`, `User.Read`, and `Directory.Read.All`
- **Better permission management**: Ensures proper scope requests for sensitive directory information

### Enhanced Diagnostics
- **Extension attribute validation**: Added logging for missing `onPremisesExtensionAttributes` to aid troubleshooting
- **Azure AD permission checking**: Helps administrators identify when Azure AD app permissions are insufficient
- **Improved error context**: More descriptive error messages with API endpoint URLs

## 🔧 Improvements

### Microsoft Graph API Integration
- **Consistent endpoint usage**: Always use the full Graph API endpoint with explicit field selection
- **Reliable extension attribute retrieval**: Ensures `onPremisesExtensionAttributes` are consistently requested
- **Better API response handling**: Enhanced validation and fallback mechanisms

### Code Quality
- **Comprehensive documentation**: Enhanced PHPDoc comments for all methods with detailed parameters and return values
- **Cleaner codebase**: Removed temporary debugging code and improved maintainability
- **Better error handling**: More specific error messages for easier troubleshooting

## 🐛 Bug Fixes

- Fixed Graph API endpoint usage to consistently request extension attributes
- Improved error handling in `buildUserinfo()` method with more specific error messages
- Enhanced logging context with URL information for better debugging

## 🔒 Security Enhancements

- Ensured proper scope requests for sensitive directory information
- Enhanced validation of user profile data from Azure AD
- Improved error handling to prevent information disclosure

## 🛠️ Technical Details

### New Methods
```php
public function getScopes(): array
```
Returns the OAuth2 scopes required for Microsoft Graph API access.

### Enhanced Methods
- `retrieveUserInfo()`: Added validation for extension attributes with logging
- `buildUserinfo()`: Always uses full Graph API endpoint for consistent data retrieval
- Error logging throughout: Enhanced with more descriptive messages and context

### API Changes
- **Backward Compatible**: All changes are backward compatible
- **No Breaking Changes**: Existing configurations will continue to work
- **Enhanced Functionality**: New features are automatically available

## 📋 Upgrade Instructions

1. **Backup your site**: Always backup before upgrading
2. **Update the module**: Replace module files with the new version
3. **Clear caches**: Run `drush cr` or clear caches via admin interface
4. **Verify functionality**: Test authentication flow in development first

## 🔍 Configuration Recommendations

### Azure AD Application Settings
Ensure your Azure AD application has the following API permissions:
- `User.Read` (delegated)
- `Directory.Read.All` (delegated or application)
- `openid`, `profile`, `email` (delegated)

### Monitoring
- Monitor logs for extension attribute warnings
- Check Azure AD app permissions if attributes are missing
- Verify Graph API endpoint responses in development

## 🚨 Known Issues

- None identified in this release

## 🔗 Dependencies

- Drupal Core: ^11
- OpenID Connect module: ^3.0
- PHP: >=8.1

## 📞 Support

For questions or issues:
- **Email**: it-support@howard.edu
- **Documentation**: [docs/README.md](docs/README.md)
- **Issues**: GitHub Issues (if applicable)

## 🔄 Next Steps

After deployment:
1. Monitor authentication logs for any new warnings
2. Verify extension attributes are being retrieved properly
3. Test group-based role mapping functionality
4. Document any site-specific configuration changes

---

**Prepared by:** Howard University IT Services  
**Date:** October 30, 2025  
**Module Version:** 11.0.8
