<?php

namespace Drupal\howard_openid_connect_windows_aad\Plugin\OpenIDConnectClient;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;

/**
 * Generic OpenID Connect client.
 *
 * Used primarily to login to Drupal sites powered by oauth2_server or PHP
 * sites powered by oauth2-server-php.
 *
 * @OpenIDConnectClient(
 *   id = "windows_aad",
 *   label = @Translation("Howard Windows Azure AD")
 * )
 */
class HowardWindowsAad extends OpenIDConnectClientBase {

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   *
   * @param array $form
   *   Windows AAD form array containing form elements.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   Submitted form values.
   *
   * @return array
   *   Renderable form array with form elements.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['enable_single_sign_out'] = [
      '#title' => $this->t('Enable Single Sign Out'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->configuration['enable_single_sign_out']) ? $this->configuration['enable_single_sign_out'] : false,
      '#description' => $this->t('Checking this option will enable Single Sign Out to occur so long as the logout url has been set to (http(s)://yoursite.com/openid-connect/windows_aad/signout) in your Azure AD registered app settings. If a user logs out of the Drupal app then they will be logged out of their SSO session elsewhere as well. Conversely if a user signs out of their SSO account elsewhere, such as Office 365, they will also be logged out of this app.'),
    ];
    $form['authorization_endpoint_wa'] = [
      '#title' => $this->t('Authorization endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['authorization_endpoint_wa'],
    ];
    $form['token_endpoint_wa'] = [
      '#title' => $this->t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['token_endpoint_wa'],
    ];
    $form['map_ad_groups_to_roles'] = [
      '#title' => $this->t('Map user\'s AD groups to Drupal roles'),
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
      '#default_value' => $this->configuration['group_mapping']['mappings'],
      '#description' => $this->t('Add one role|group(s) mapping per line. Role and Group should be separated by "|". Multiple groups can be mapped to a single role on the same line using ";" to separate the groups. Ideally you should use the group id since it is immutable, but the title (displayName) may also be used.'),
    ];

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   *
   * @return array
   *   Endpoint details with authorization endpoints, user access token and
   *   userinfo object.
   */
  public function getEndpoints() {
    return [
      'authorization' => $this->configuration['authorization_endpoint_wa'],
      'token' => $this->configuration['token_endpoint_wa'],
    ];
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   *
   * @param string $authorization_code
   *   A authorization code string.
   *
   * @return array|bool
   *   A result array or false.
   */
  public function retrieveTokens($authorization_code) {
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

    /* @var \GuzzleHttp\ClientInterface $client */
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
   * Implements OpenIDConnectClientInterface::retrieveUserInfo().
   *
   * @param string $access_token
   *   An access token string.
   *
   * @return array|bool
   *   A result array or false.
   */
  public function retrieveUserInfo($access_token) {

    // Get user info from microsoft graph api.
    $userinfo = $this->buildUserinfo($access_token, 'https://graph.microsoft.com/v1.0/me?$select=id,displayName,givenName,surname,jobTitle,mail,userPrincipalName,officeLocation,onPremisesExtensionAttributes', 'userPrincipalName', 'displayName');

    // TEMP LOG
    // \Drupal::logger('howard_aad_userinfo_retrieved_from_api')->notice('<pre><code>' . print_r($userinfo, TRUE) . '</code></pre>');

    // If AD group to Drupal role mapping has been enabled then attach group
    // data from a graph API if configured to do so.
    if ($this->configuration['map_ad_groups_to_roles']) {
      $userinfo['groups'] = $this->retrieveGroupInfo($access_token);
    }

    return $userinfo;
  }

  /**
   * Helper function to do the call to the endpoint and build userinfo array.
   *
   * @param string $access_token
   *   The access token.
   * @param string $url
   *   The endpoint we want to send the request to.
   * @param string $upn
   *   The name of the property that holds the Azure username.
   * @param string $name
   *   The name of the property we want to map to Drupal username.
   *
   * @return array
   *   The userinfo array. Empty array if unsuccessful.
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
        // if not, add the principal name as email, so Drupal still will
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
   * Calls a graph api to retrieve teh user's group membership information.
   *
   * @param string $access_token
   *   An access token string.
   *
   * @return array
   *   An array of group informaion.
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
