<?php

namespace Drupal\howard_openid_connect_windows_aad\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Utility\Error;

/**
 * Listens to the dynamic route events.
 */
class HowardWindowsAadSSORouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
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
