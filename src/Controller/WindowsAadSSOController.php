<?php

declare(strict_types=1);

/**
 * @file
 * Contains \Drupal\howard_openid_connect_windows_aad\Controller\WindowsAadSSOController.
 */

namespace Drupal\howard_openid_connect_windows_aad\Controller;

use Drupal\Core\Controller\ControllerBase;
use Psr\Log\LoggerInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\openid_connect\OpenIDConnectAuthmap;

/**
 * Controller for Azure AD Single Sign-On and Single Sign-Out operations.
 *
 * This controller handles authentication flows for Howard University's
 * integration with Microsoft Azure Active Directory, providing:
 *
 * - Single Sign-Out (SSO) callback handling
 * - User logout with Azure AD integration
 * - Security validation and CSRF protection
 * - Comprehensive logging and error handling
 *
 * The controller integrates with Azure AD's logout endpoints to provide
 * seamless authentication experiences across Howard University's applications
 * and Microsoft Office 365 services.
 *
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc#single-sign-out
 */
class WindowsAadSSOController extends ControllerBase {

  /**
   * The logger service for recording authentication events and errors.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The OpenID Connect authentication mapping service.
   *
   * This service manages the mapping between Drupal user accounts
   * and external OpenID Connect provider accounts (Azure AD).
   *
   * @var \Drupal\openid_connect\OpenIDConnectAuthmap
   */
  protected $authmap;

  /**
   * Constructs a WindowsAadSSOController object.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service for recording authentication events and errors.
   * @param \Drupal\openid_connect\OpenIDConnectAuthmap $authmap
   *   The OpenID Connect authentication mapping service.
   */
  public function __construct(LoggerInterface $logger, OpenIDConnectAuthmap $authmap) {
    $this->logger = $logger;
    $this->authmap = $authmap;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('howard_openid_connect_windows_aad'),
      $container->get('openid_connect.authmap')
    );
  }

  /**
   * Handles Single Sign-Out callback from Azure Active Directory.
   *
   * This method is called by Azure AD when a user logs out of their SSO
   * session from another application (such as Office 365 or other Microsoft
   * services). It implements the OpenID Connect RP-Initiated Logout specification.
   *
   * The method performs the following security and validation checks:
   * 1. Verifies that the Windows AAD client is enabled
   * 2. Confirms that Single Sign-Out is enabled in configuration
   * 3. Validates that the current user has a connected Azure AD account
   * 4. Logs out the user from Drupal if all conditions are met
   * 5. Returns appropriate HTTP status codes for security
   *
   * Security considerations:
   * - Returns HTTP 403 for misconfigured or potential CSRF attempts
   * - Logs warnings for suspicious signout attempts
   * - Only logs out users with valid connected accounts
   * - Returns HTTP 200 for successful operations
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   HTTP 200 (OK) response if Single Sign-Out is properly configured
   *   and user logout is successful or not needed.
   *   HTTP 403 (Forbidden) response if Single Sign-Out is not enabled
   *   or appears to be a malicious attempt.
   *
   * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html
   * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc#single-sign-out
   */
  public function signout() {
    $configuration = $this->config('openid_connect.settings.windows_aad');
    $settings = $configuration->get('settings');
    $enabled = $configuration->get('enabled');
    // Check that the windows_aad client is enabled and so is SSOut.
    if ($enabled && isset($settings['enable_single_sign_out']) && $settings['enable_single_sign_out']) {
      // Ensure the user has a connected account.
      $user = \Drupal::currentUser();
      $connected_accounts = $this->authmap->getConnectedAccounts($user);
      $connected = ($connected_accounts && isset($connected_accounts['windows_aad']));
      $logged_in = $user->isAuthenticated();
      // Only log the user out if they are logged in and have a connected
      // account. Return a 200 OK in any case since all is good.
      if ($logged_in && $connected) {
        user_logout();
      }
      return new Response('', Response::HTTP_OK);
    }
    // Likely a misconfiguration since SSOut attempts should not be made to the
    // logout uri unless it has been configured in Azure AD; if you had
    // configured it in Azure AD then you should have also enabled SSOut in the
    // OpenID Connect settings. Also, a possible malicious CSRF attempt. Log a
    // warning either way.
    $this->logger->warning('Howard Windows AAD Single Sign Out attempt, but SSOut has not been enabled in the OpenID Connect Windows AAD configuration.');
    return new Response('', Response::HTTP_FORBIDDEN);
  }

  /**
   * Handles user logout with Azure AD Single Sign-Out integration.
   *
   * This method extends the standard Drupal user logout functionality to
   * integrate with Azure Active Directory's Single Sign-Out (SSO) feature.
   * When enabled, users are redirected to Azure AD's logout endpoint to
   * sign out of all connected Microsoft services.
   *
   * The logout process performs the following operations:
   * 1. Checks if Azure AD SSO and Single Sign-Out are enabled
   * 2. Verifies user has a connected Azure AD account
   * 3. Logs the user out of Drupal using standard logout procedures
   * 4. Redirects to Azure AD logout endpoint if SSO is configured
   * 5. Falls back to standard Drupal logout if SSO is not configured
   *
   * Azure AD logout features:
   * - Signs user out of all Microsoft Office 365 services
   * - Invalidates Azure AD session tokens
   * - Redirects back to the Drupal site after logout
   * - Prevents page caching during logout process
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to either:
   *   - Azure AD logout endpoint with post_logout_redirect_uri parameter
   *     (when Single Sign-Out is enabled and user has connected account)
   *   - Drupal front page (when Single Sign-Out is not configured)
   *
   * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc#single-sign-out
   * @see user_logout()
   */
  public function logout() {
    $connected = FALSE;
    $configuration = $this->config('openid_connect.settings.windows_aad');
    $settings = $configuration->get('settings');
    // Check that the windows_aad client is enabled and so is SSOut.
    $enabled = (($configuration->get('enabled')) && isset($settings['enable_single_sign_out']) && $settings['enable_single_sign_out']);

    // Check for a connected account before we log the Drupal user out.
    if ($enabled) {
      // Ensure the user has a connected account.
      $user = \Drupal::currentUser();
      $connected_accounts = $this->authmap->getConnectedAccounts($user);
      $connected = ($connected_accounts && isset($connected_accounts['windows_aad']));
    }

    user_logout();
    if ($connected) {
      // Redirect back to the home page once signed out.
      $redirect_uri = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(TRUE)->getGeneratedUrl();
      $query_parameters = [
        'post_logout_redirect_uri' => $redirect_uri,
      ];
      $query = UrlHelper::buildQuery($query_parameters);

      $response = new TrustedRedirectResponse('https://login.microsoftonline.com/common/oauth2/v2.0/logout?' . $query);
      // We can't cache the response, since we need the user to get logged out
      // prior to being redirected. The kill switch will prevent the page
      // getting cached when page cache is active.
      \Drupal::service('page_cache_kill_switch')->trigger();
      return $response;
    }
    // No SSOut so do the usual thing and redirect to the front page.
    return $this->redirect('<front>');
  }

}
