<?php

/**
 * @file
 * Contains \Drupal\uw_auth\Authentication\Provider\UWAuth.
 */

namespace Drupal\uw_auth\Authentication\Provider;

use Drupal\Core\Url;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class UWAuth.
 *
 * @package Drupal\uw_auth\Authentication\Provider
 */
class UWAuth implements AuthenticationProviderInterface {

  /**
   * Checks whether suitable authentication credentials are on the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return bool
   *   TRUE if authentication credentials suitable for this provider are on the
   *   request, FALSE otherwise.
   */
  public function applies(Request $request) {
    // If you return TRUE and the method Authentication logic fails,
    // you will get out from Drupal navigation if you are logged in.
    //return false;
    return (
  	  $request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')) != '' &&
  	  $request->server->get(\Drupal::config('uw_auth.settings')->get('email_field')) != '' &&
  	  $request->query->get('shiblogin') == '1'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    if(\Drupal::config('uw_auth.settings')->get('force_uw_groups')){
      $NetIDGroups = new \Drupal\uw_groups\NetIDGroups();

      if($request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')) != ''){
        if(!$NetIDGroups->isNetIDInAnyActiveGroup($request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')))){
          return null;
        }
      }else{
        return null;
      }
    }

    // Find the user
    // $account_search = $this->entityManager->getStorage('user')->loadByProperties(array('name' => $request->server->get(\Drupal::config('uw_auth.settings')->get('username_field'))));
    $account = user_load_by_name($request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')));

    // Create the user
    if(\Drupal::config('uw_auth.settings')->get('autocreate_accounts') && !$account){
      $account = \Drupal\user\Entity\User::create();
      $account->setPassword(str_shuffle(md5(microtime()*rand(15,99999)).md5(microtime()))); // Set a dummy password
      $account->enforceIsNew();
      $account->setEmail($request->server->get(\Drupal::config('uw_auth.settings')->get('email_field')));
      $account->setUsername($request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')));
      $account->activate();
      $account->save();
    }elseif(!$account){
      return null;
    }



    if($account){
      user_login_finalize($account);

      return $account;
    }else{
      return null;
    }
  }

}
