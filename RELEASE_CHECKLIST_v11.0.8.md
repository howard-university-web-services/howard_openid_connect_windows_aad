# Release Checklist for v11.0.8

## Pre-Release Tasks

### Code Quality
- [x] Code changes documented and tested
- [x] PHPDoc comments updated
- [x] Temporary debug code removed
- [x] All methods properly documented

### Documentation Updates
- [x] CHANGELOG.md updated with v11.0.8 changes
- [x] Version number updated in `howard_openid_connect_windows_aad.info.yml`
- [x] Release notes created (`RELEASE_NOTES_v11.0.8.md`)
- [ ] README.md updated if needed

### Version Management
- [x] Version number: 11.0.8
- [x] Release type: Minor enhancement
- [x] Backward compatibility maintained

## Release Process

### 1. Final Code Review
```bash
# Review all changes
git diff HEAD~1

# Check file status
git status
```

### 2. Commit Changes
```bash
# Add all changes
git add .

# Commit with descriptive message
git commit -m "Release v11.0.8: Enhanced OAuth2 scope management and improved Graph API integration

- Add getScopes() method for explicit OAuth2 scope definitions
- Enhanced logging for Azure AD extension attributes
- Improved Graph API endpoint usage consistency  
- Better error handling and diagnostic capabilities
- Comprehensive PHPDoc documentation updates
- Cleaned up temporary debugging code"
```

### 3. Create Release Tag
```bash
# Create annotated tag
git tag -a v11.0.8 -m "Release v11.0.8: Enhanced OAuth2 scope management and improved Graph API integration"

# Push commits and tags
git push origin master
git push origin v11.0.8
```

### 4. Quality Assurance
- [ ] Run PHPCS checks: `composer phpcs`
- [ ] Run PHPStan analysis: `composer phpstan`
- [ ] Test authentication flow in development environment
- [ ] Verify extension attributes are logged properly
- [ ] Test group-based role mapping

### 5. Deployment Preparation
- [ ] Backup current production environment
- [ ] Document rollback procedure
- [ ] Prepare deployment communications
- [ ] Schedule maintenance window if needed

## Post-Release Tasks

### 1. Verification
- [ ] Verify authentication flow works correctly
- [ ] Check logs for new warning messages
- [ ] Confirm Azure AD integration is functioning
- [ ] Test user creation and role mapping

### 2. Monitoring
- [ ] Monitor authentication logs for 24-48 hours
- [ ] Watch for any extension attribute warnings
- [ ] Verify Graph API performance
- [ ] Check error rates and user feedback

### 3. Documentation
- [ ] Update internal documentation
- [ ] Share release notes with stakeholders
- [ ] Update any deployment guides
- [ ] Archive release documentation

## Rollback Plan

If issues are encountered:

1. **Immediate rollback**: Replace files with previous version (v11.0.7)
2. **Clear caches**: `drush cr` or admin interface
3. **Verify functionality**: Test authentication flow
4. **Investigate issues**: Review logs and error messages
5. **Plan hotfix**: If needed, prepare patch release

## Key Changes Summary

- **New OAuth2 scope management** with `getScopes()` method
- **Enhanced diagnostic logging** for Azure AD extension attributes  
- **Improved Graph API integration** with consistent endpoint usage
- **Better error handling** throughout authentication flow
- **Comprehensive documentation** updates

## Dependencies

- Drupal Core: ^11
- OpenID Connect: ^3.0  
- PHP: >=8.1

## Notes

- All changes are backward compatible
- No configuration changes required
- Azure AD app permissions should be verified
- Monitor for extension attribute warnings

---

**Prepared by:** Development Team  
**Date:** October 30, 2025  
**Release:** v11.0.8
