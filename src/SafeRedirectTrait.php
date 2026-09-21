<?php

namespace Drupal\uw_auth;

/**
 * Shared helper for building a same-origin redirect path from a URL.
 */
trait SafeRedirectTrait {

  /**
   * Builds a same-origin redirect path (with query/fragment) from a URL.
   *
   * @param array $parts
   *   The result of parse_url() on the target URL.
   *
   * @return string
   *   A path guaranteed to start with exactly one forward slash.
   */
  protected function buildSafeRedirectPath(array $parts) {
    $redirect = $parts['path'] ?? '/';
    $redirect = $redirect === '' ? '/' : $redirect;

    // Collapse any leading slashes or backslashes to a single leading slash.
    // Browsers normalize a leading backslash to a forward slash, so a path
    // like "/\attacker" or "\/attacker" would otherwise be treated as the
    // scheme-relative "//attacker" and redirect off-site.
    $redirect = '/' . ltrim($redirect, '/\\');

    if (isset($parts['query'])) {
      $redirect .= '?' . $parts['query'];
    }
    if (isset($parts['fragment'])) {
      $redirect .= '#' . $parts['fragment'];
    }

    return $redirect;
  }

}
