<?php

declare(strict_types=1);

/**
 * @file
 * Contains the HowardWindowsAadSSORouteSubscriber for dynamic route management.
 *
 * This file provides the route subscriber responsible for dynamically modifying
 * Drupal's routing system to integrate Azure Active Directory Single Sign-Out
 * functionality with Howard University's authentication requirements.
 *
 * @package Drupal\howard_openid_connect_windows_aad\Routing
 * @author Howard University Web Team
 * @copyright 2024 Howard University
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

namespace Drupal\howard_openid_connect_windows_aad\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Utility\Error;

/**
 * Route subscriber for Azure AD Single Sign-Out integration.
 *
 * This route subscriber dynamically modifies Drupal's routing system to
 * integrate Azure Active Directory Single Sign-Out functionality with
 * the standard user logout process.
 *
 * Key functionality:
 * - Listens to dynamic route events during route collection
 * - Conditionally overrides the default user.logout route controller
 * - Enables Azure AD SSO logout when properly configured
 * - Provides comprehensive error handling and logging
 * - Maintains backward compatibility with standard Drupal logout
 *
 * The subscriber only modifies routing when:
 * 1. Azure AD OpenID Connect client is enabled
 * 2. Single Sign-Out feature is enabled in configuration
 * 3. Configuration is valid and accessible
 *
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc#single-sign-out
 * @see \Drupal\Core\Routing\RouteSubscriberBase
 */
class HowardWindowsAadSSORouteSubscriber extends RouteSubscriberBase {

  /**
   * Modifies the user logout route to support Azure AD Single Sign-Out.
   *
   * This method conditionally overrides the default user.logout route
   * controller to enable Azure Active Directory Single Sign-Out integration.
   * The override only occurs when Azure AD SSO is properly configured.
   *
   * The method performs the following operations:
   * 1. Retrieves the existing user.logout route from the collection
   * 2. Attempts to load Azure AD OpenID Connect configuration
   * 3. Validates that the client is enabled and SSO is configured
   * 4. Overrides the route controller if conditions are met
   * 5. Provides comprehensive error handling for configuration issues
   *
   * Configuration validation includes:
   * - Azure AD OpenID Connect client is enabled
   * - Single Sign-Out feature is enabled in settings
   * - Configuration is accessible and valid
   *
   * Error handling:
   * - Catches configuration loading exceptions
   * - Logs detailed error information for troubleshooting
   * - Gracefully falls back to standard logout on failures
   * - Continues operation without breaking the logout functionality
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection containing all application routes.
   *
   * @see \Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController::logout()
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('user.logout')) {
      try {
        $configuration = \Drupal::config('openid_connect.settings.windows_aad');
        $settings = $configuration->get('settings');
        $enabled = $configuration->get('enabled');
      }
      catch (\Exception $exception) {
        // Not important to differentiate between Exceptions here, we just need
        // make it know that something is wrong and we won't enable SSOut.
        $configuration = FALSE;
        $variables = Error::decodeException($exception);
        \Drupal::logger('howard_openid_connect_windows_aad')->error('Failed to check OpenID Connect Windows AAD configuration so Single Sign Off will remain disabled. %type: @message in %function (line %line of %file).', $variables);
      }
      // Override the controller for the user.logout route in order to redirect
      // to the Windows Azure AD Single Sign out endpoint if SSOut is enabled.
      if ($configuration && $enabled && isset($settings['enable_single_sign_out']) && $settings['enable_single_sign_out']) {
        $route->setDefault('_controller', '\Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController::logout');
      }
    }
  }

}
