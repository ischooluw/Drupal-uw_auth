<?php

namespace Drupal\uw_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\uw_auth\SafeRedirectTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles the post-SAML Drupal login callback.
 */
class UWLoginController extends ControllerBase {

  use SafeRedirectTrait;

  /**
   * Redirect to the requested target after Drupal has finalized login.
   */
  public function login(Request $request) {
    $redirect = '/';
    $target = $request->query->get('target');

    if ($target && filter_var($target, FILTER_VALIDATE_URL)) {
      $redirect = $this->buildSafeRedirectPath(parse_url($target));
    }
    elseif (is_string($target) && strpos($target, '/') === 0) {
      $redirect = $this->buildSafeRedirectPath(['path' => $target]);
    }

    $request->getSession()->save();

    return new RedirectResponse($redirect);
  }

}
