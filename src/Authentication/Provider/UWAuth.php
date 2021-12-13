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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager) {
    $this->configFactory = $config_factory;
    $this->entityManager = $entity_manager;
  }

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
          throw new AccessDeniedHttpException();
          return null;
        }
      }else{
        throw new AccessDeniedHttpException();
        return null;
      }
    }

    // Find the user
    $account_search = $this->entityManager->getStorage('user')->loadByProperties(array('name' => $request->server->get(\Drupal::config('uw_auth.settings')->get('username_field'))));

    // Create the user
    if(\Drupal::config('uw_auth.settings')->get('autocreate_accounts') && !$account = reset($account_search)){
      $account = \Drupal\user\Entity\User::create();
      $account->setPassword(str_shuffle(md5(microtime()*rand(15,99999)).md5(microtime()))); // Set a dummy password
      $account->enforceIsNew();
      $account->setEmail($request->server->get(\Drupal::config('uw_auth.settings')->get('email_field')));
      $account->setUsername($request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')));
      $account->activate();
      $account->save();
    }elseif(!$account = reset($account_search)){
      throw new AccessDeniedHttpException();
      return null;
    }



    if($account){

      user_login_finalize($account);

      return $account;
    }else{
      throw new AccessDeniedHttpException();
      return null;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function cleanup(Request $request) {}

  /**
   * {@inheritdoc}
   */
  public function handleException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();
    if ($exception instanceof AccessDeniedHttpException) {
      $event->setException(new UnauthorizedHttpException('Invalid consumer origin.', $exception));
      return TRUE;
    }
    return FALSE;
  }

}
