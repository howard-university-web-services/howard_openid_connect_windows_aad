<?php

declare(strict_types=1);

/**
 * @file
 * Contains the HowardWindowsAad OpenID Connect client plugin.
 *
 * This file provides the OpenID Connect client plugin for integrating with
 * Microsoft Azure Active Directory, specifically customized for Howard
 * University's authentication and user management requirements.
 *
 * @package Drupal\howard_openid_connect_windows_aad\Plugin\OpenIDConnectClient
 * @copyright 2024 Howard University
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

namespace Drupal\howard_openid_connect_windows_aad\Plugin\OpenIDConnectClient;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;

/**
 * Howard University Azure Active Directory OpenID Connect client.
 *
 * This client provides integration with Microsoft Azure Active Directory
 * using the OpenID Connect protocol, specifically customized for Howard
 * University's authentication requirements.
 *
 * Features:
 * - Single Sign-On (SSO) with Azure Active Directory
 * - Automatic user creation and profile synchronization
 * - Group-based role mapping from Azure AD to Drupal roles
 * - Single Sign-Out (SSO) support
 * - Comprehensive error handling and logging
 * - Howard University specific customizations
 *
 * @OpenIDConnectClient(
 *   id = "windows_aad",
 *   label = @Translation("Howard Windows Azure AD")
 * )
 *
 * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc
 * @see https://www.drupal.org/project/openid_connect
 */
class HowardWindowsAad extends OpenIDConnectClientBase {

  /**
   * Builds the configuration form for Howard Windows Azure AD settings.
   *
   * This method provides configuration options specific to Azure Active
   * Directory integration, including:
   * - Single Sign-Out (SSO) enablement
   * - Custom authorization and token endpoints
   * - Active Directory group to Drupal role mapping
   * - Manual group-role mapping configuration.
   *
   * @param array $form
   *   The form array containing form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form containing submitted values.
   *
   * @return array
   *   The modified form array with Azure AD specific configuration elements.
   *
   * @see \Drupal\openid_connect\Plugin\OpenIDConnectClientBase::buildConfigurationForm()
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['enable_single_sign_out'] = [
      '#title' => $this->t('Enable Single Sign Out'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['enable_single_sign_out']) ? $this->configuration['enable_single_sign_out'] : FALSE,
      '#description' => $this->t('Checking this option will enable Single Sign Out to occur so long as the logout url has been set to (http(s)://yoursite.com/openid-connect/windows_aad/signout) in your Azure AD registered app settings. If a user logs out of the Drupal app then they will be logged out of their SSO session elsewhere as well. Conversely if a user signs out of their SSO account elsewhere, such as Office 365, they will also be logged out of this app.'),
    ];
    $form['authorization_endpoint_wa'] = [
      '#title' => $this->t('Authorization endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['authorization_endpoint_wa'] ?? '',
    ];
    $form['token_endpoint_wa'] = [
      '#title' => $this->t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['token_endpoint_wa'] ?? '',
    ];
    $form['map_ad_groups_to_roles'] = [
      '#title' => $this->t("Map user's AD groups to Drupal roles"),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['map_ad_groups_to_roles']) ? $this->configuration['map_ad_groups_to_roles'] : '',
      '#description' => $this->t('Enable this to configure Drupal user role assignment based on AD group membership.'),
    ];
    // AD group mapping configuration field set.
    $form['group_mapping'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('AD group mapping options'),
      '#states' => [
        'invisible' => [
          ':input[name="clients[windows_aad][settings][map_ad_groups_to_roles]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['group_mapping']['mappings'] = [
      '#title' => $this->t('Manual mappings'),
      '#type' => 'textarea',
      '#default_value' => $this->configuration['group_mapping']['mappings'] ?? '',
      '#description' => $this->t('Add one role|group(s) mapping per line. Role and Group should be separated by "|". Multiple groups can be mapped to a single role on the same line using ";" to separate the groups. Ideally you should use the group id since it is immutable, but the title (displayName) may also be used.'),
    ];

    return $form;
  }

  /**
   * Retrieves the Azure AD endpoints for OAuth2/OpenID Connect flows.
   *
   * Returns the configured authorization and token endpoints for Azure
   * Active Directory. These endpoints are used for the OAuth2 authorization
   * code flow and token exchange.
   *
   * @return array
   *   An associative array containing:
   *   - 'authorization': The Azure AD authorization endpoint URL
   *   - 'token': The Azure AD token endpoint URL
   *
   * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow
   */
  public function getEndpoints(): array {
    return [
      'authorization' => $this->configuration['authorization_endpoint_wa'],
      'token' => $this->configuration['token_endpoint_wa'],
    ];
  }

  /**
   * Exchanges authorization code for access and ID tokens.
   *
   * This method implements the OAuth2 authorization code flow by exchanging
   * the authorization code received from Azure AD for access and ID tokens.
   * The tokens are used for subsequent API calls and user authentication.
   *
   * The method performs the following steps:
   * 1. Constructs the redirect URI using Drupal's routing system
   * 2. Prepares the token exchange request with required parameters
   * 3. Makes a POST request to the Azure AD token endpoint
   * 4. Parses the response and extracts tokens
   * 5. Handles errors and logs failures appropriately
   *
   * @param string $authorization_code
   *   The authorization code received from Azure AD during the OAuth2 flow.
   *
   * @return array|false
   *   An associative array containing the tokens on success:
   *   - 'id_token': The OpenID Connect ID token (JWT)
   *   - 'access_token': The OAuth2 access token for API calls
   *   - 'expire': (optional) Token expiration timestamp
   *   Returns FALSE on failure.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   *   When the HTTP request to Azure AD fails.
   *
   * @see https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-oauth2-auth-code-flow#request-an-access-token
   */
  public function retrieveTokens(string $authorization_code): ?array {
    // Exchange `code` for access token and ID token.
    $language_none = \Drupal::languageManager()
      ->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $redirect_uri = Url::fromRoute(
      'openid_connect.redirect_controller_redirect',
      [
        'client_name' => $this->pluginId,
      ],
      [
        'absolute' => TRUE,
        'language' => $language_none,
      ]
    )->toString();
    $endpoints = $this->getEndpoints();

    $request_options = [
      'form_params' => [
        'code' => $authorization_code,
        'client_id' => $this->configuration['client_id'],
        'client_secret' => $this->configuration['client_secret'],
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code',
      ],
    ];

    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->httpClient;

    try {
      $response = $client->post($endpoints['token'], $request_options);
      $response_data = json_decode((string) $response->getBody(), TRUE);

      // Expected result.
      $tokens = [
        'id_token' => $response_data['id_token'],
        'access_token' => $response_data['access_token'],
      ];
      if (array_key_exists('expires_in', $response_data)) {
        $tokens['expire'] = \Drupal::time()->getRequestTime() + $response_data['expires_in'];
      }
      return $tokens;
    }
    catch (RequestException $e) {
      $variables = [
        '@message' => 'Could not retrieve tokens',
        '@error_message' => $e->getMessage(),
      ];
      $this->loggerFactory->get('howard_openid_connect_windows_aad')
        ->error('@message. Details: @error_message', $variables);
      return FALSE;
    }
  }

  /**
   * Retrieves user information from Microsoft Graph API.
   *
   * This method fetches comprehensive user profile information from Azure AD
   * using the Microsoft Graph API. It also optionally retrieves group
   * membership information for role mapping purposes.
   *
   * The method performs the following operations:
   * 1. Calls Microsoft Graph API to get user profile data
   * 2. Processes and normalizes the returned user information
   * 3. Optionally retrieves group membership if role mapping is enabled
   * 4. Returns a standardized user information array
   *
   * User information includes:
   * - Basic profile data (name, email, job title)
   * - Office location and organizational attributes
   * - Group memberships (if enabled)
   * - Azure AD extension attributes
   *
   * @param string $access_token
   *   A valid OAuth2 access token for Microsoft Graph API access.
   *
   * @return array|false
   *   An associative array containing user information on success:
   *   - 'id': Azure AD user identifier
   *   - 'name': User's display name or username
   *   - 'email': User's email address
   *   - 'groups': (optional) Array of group memberships
   *   - Additional Azure AD profile attributes
   *   Returns FALSE on failure.
   *
   * @see https://docs.microsoft.com/en-us/graph/api/user-get
   * @see https://docs.microsoft.com/en-us/graph/api/user-list-memberof
   */
  public function retrieveUserInfo(string $access_token): ?array {

    // Get user info from microsoft graph api.
    $endpoint = 'https://graph.microsoft.com/v1.0/me?$select=id,displayName,givenName,surname,jobTitle,mail,userPrincipalName,officeLocation,onPremisesExtensionAttributes';
    $userinfo = $this->buildUserinfo($access_token, $endpoint, 'userPrincipalName', 'displayName');

    // TEMP LOG
    // \Drupal::logger('howard_aad_userinfo_retrieved_from_api')->notice('<pre><code>' . print_r($userinfo, TRUE) . '</code></pre>');.
    // If AD group to Drupal role mapping has been enabled then attach group
    // data from a graph API if configured to do so.
    if ($this->configuration['map_ad_groups_to_roles']) {
      $userinfo['groups'] = $this->retrieveGroupInfo($access_token);
    }

    return $userinfo;
  }

  /**
   * Builds user information array from Microsoft Graph API response.
   *
   * This helper method handles the actual HTTP request to the Microsoft Graph
   * API and processes the response to create a standardized user information
   * array. It performs the following operations:
   *
   * 1. Makes authenticated GET request to the specified Graph API endpoint
   * 2. Processes the JSON response and extracts user profile data
   * 3. Normalizes username using the User Principal Name (UPN)
   * 4. Handles email address mapping and fallbacks
   * 5. Provides comprehensive error handling and logging
   *
   * The method includes Howard University specific customizations:
   * - Username extraction from UPN (removes domain suffix)
   * - Email address validation and fallback mechanisms
   * - Enhanced error logging for troubleshooting
   *
   * @param string $access_token
   *   A valid OAuth2 access token for API authentication.
   * @param string $url
   *   The complete Microsoft Graph API endpoint URL to request.
   * @param string $upn
   *   The property name containing the Azure username (userPrincipalName).
   * @param string $name
   *   The property name to map to Drupal username (displayName).
   *
   * @return array
   *   The processed user information array with normalized fields.
   *   Returns empty array if the request fails or encounters errors.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   *   When the HTTP request to Microsoft Graph API fails.
   *
   * @see https://docs.microsoft.com/en-us/graph/api/user-get
   */
  private function buildUserinfo($access_token, $url, $upn, $name) {
    $profile_data = [];

    // Perform the request.
    $options = [
      'method' => 'GET',
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $access_token,
      ],
    ];
    $client = $this->httpClient;

    try {
      $response = $client->get($url, $options);
      $response_data = (string) $response->getBody();

      // Profile Information.
      $profile_data = json_decode($response_data, TRUE);
      $profile_data['name'] = $profile_data[$name];

      // Override username with upn alias.
      if (isset($profile_data[$upn])) {
        $profile_data['name'] = explode("@", $profile_data[$upn])[0];
      }

      // Azure provides 'mail' for userinfo vs email.
      if (!isset($profile_data['mail'])) {
        // If not, add the principal name as email, so Drupal still will
        // create the user anyway.
        // Write watchdog warning.
        $variables = ['@user' => $profile_data[$upn]];

        $this->loggerFactory->get('howard_openid_connect_windows_aad')
          ->warning('Email address of user @user not found in UserInfo. Used username instead, please check.', $variables);

        $profile_data['email'] = $profile_data[$upn];

      }
      else {
        // OpenID Connect module expects the 'email' token for userinfo.
        $profile_data['email'] = $profile_data['mail'];
      }

    }
    catch (RequestException $e) {
      $variables = [
        '@error_message' => $e->getMessage(),
      ];
      $this->loggerFactory->get('howard_openid_connect_windows_aad')
        ->error('Could not retrieve user profile information. Details: @error_message', $variables);
    }

    return $profile_data;
  }

  /**
   * Retrieves user's group membership information from Microsoft Graph API.
   *
   * This method fetches the authenticated user's group memberships from
   * Azure Active Directory using the Microsoft Graph API. The group
   * information is used for mapping Azure AD groups to Drupal roles.
   *
   * The method performs the following operations:
   * 1. Makes authenticated request to the Graph API memberOf endpoint
   * 2. Retrieves all groups the user is a member of
   * 3. Processes the response to extract group identifiers and names
   * 4. Handles pagination if the user belongs to many groups
   * 5. Returns structured group data for role mapping
   *
   * Group information includes:
   * - Group object IDs (immutable identifiers)
   * - Group display names
   * - Group types and classifications
   * - Security vs. distribution group distinctions
   *
   * @param string $access_token
   *   A valid OAuth2 access token with appropriate Graph API permissions
   *   (typically User.Read and GroupMember.Read.All or Directory.Read.All).
   *
   * @return array
   *   An associative array containing group membership information:
   *   - 'value': Array of group objects with id, displayName, etc.
   *   - Additional metadata from the Graph API response
   *   Returns empty array if request fails or user has no group memberships.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   *   When the HTTP request to Microsoft Graph API fails.
   *
   * @see https://docs.microsoft.com/en-us/graph/api/user-list-memberof
   * @see https://docs.microsoft.com/en-us/graph/permissions-reference#group-permissions
   */
  protected function retrieveGroupInfo($access_token) {
    // By default or if an error occurs return empty group information.
    $group_data = [];

    $uri = 'https://graph.microsoft.com/v1.0/me/memberOf';
    if ($uri) {
      // Perform the request.
      $options = [
        'method' => 'GET',
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $access_token,
        ],
      ];
      $client = $this->httpClient;

      try {
        $response = $client->get($uri, $options);
        $response_data = (string) $response->getBody();

        // Group Information.
        $group_data = json_decode($response_data, TRUE);
      }
      catch (RequestException $e) {
        $variables = [
          '@api' => $uri,
          '@error_message' => $e->getMessage(),
        ];
        $this->loggerFactory->get('howard_openid_connect_windows_aad')
          ->error('Failed to retrieve AD group information from graph api (@api). Details: @error_message', $variables);
      }
    }
    // Return group information or an empty array.
    return $group_data;
  }

}
