<?php

namespace Drupal\uw_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the post-SAML Drupal login callback.
 */
class UWLoginController extends ControllerBase {

  /**
   * Redirect to the requested target after Drupal has finalized login.
   */
  public function login(Request $request) {
    $redirect = '/';
    $target = $request->query->get('target');

    if ($target && filter_var($target, FILTER_VALIDATE_URL)) {
      $parts = parse_url($target);
      $redirect = $parts['path'] ?? '/';
      $redirect = $redirect === '' ? '/' : $redirect;

      // prevent off-site redirects
      if (strpos($redirect, '//') === 0) {
        $redirect = '/' . ltrim($redirect, '/');
      }

      if (isset($parts['query'])) {
        $redirect .= '?' . $parts['query'];
      }
      if (isset($parts['fragment'])) {
        $redirect .= '#' . $parts['fragment'];
      }
    }
    elseif (is_string($target) && strpos($target, '/') === 0 && strpos($target, '//') !== 0) {
      $redirect = $target;
    }

    $request->getSession()->save();

    return new RedirectResponse($redirect);
  }

}
