<?php

/**
 * @file
 * Contains \Drupal\uw_auth\Plugin\Block\ShibLogin.
 */

namespace Drupal\uw_auth\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ShibLoginLink' block.
 *
 * @Block(
 *  id = "shib_login",
 *  admin_label = @Translation("Shib login link"),
 * )
 */
class ShibLoginLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;

    $current_user = \Drupal::currentUser();
    $uname = null;
    $uid = null;
    $isannon = true;
    if (!$current_user->isAnonymous()) {
      $isannon = false;
      $uid = $current_user->id();
      $uname = $current_user->getAccountName();
    }

    $shibauth = $this->_get_shiblink();

    return array(
      '#theme' => 'uw_auth',
      '#shibauth' => $shibauth,
      '#base_url' => $base_url,
      '#uid' => $uid,
      '#uname' => $uname,
      '#isannon' => $isannon,
      '#cache' => array('max-age' => 0,),
    );
  }

  // Returns the Shibboleth login link.
  private function _get_shiblink() {

    // Preserve Query string
    $query_string = \Drupal::request()->getQueryString();
    $qs = (strlen($query_string) >= 1) ? explode('&', $query_string) : array();

    // Prepend shiblogin to query string array
    array_unshift($qs,'shiblogin=1');

    // build the target url
    $path = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBaseUrl() . \Drupal::request()->getPathInfo();
    $shibll = \Drupal::config('uw_auth.settings')->get('login_link');
    $shibll = Url::fromUserInput($shibll,['absolute' => true, 'https' => true])->toString();
    $shibll = str_replace('CURRENT_PATH', $path, $shibll);

    $shibll .= '?' . implode('&',$qs);
    return $shibll;
  }
}
