<?php
/**
 * @file
 * Contains \Drupal\uw_auth\Controller\AuthController.
 */

namespace Drupal\uw_auth\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\image\Entity\ImageStyle;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class AuthController extends ControllerBase {

    /** Dependency injection **/
    protected $request;
    protected $entityManager;

    public function __construct(RequestStack $request_stack, EntityManagerInterface $entity_manager) {
      // print_r($request_stack->getCurrentRequest()->request);
      $this->request = $request_stack->getCurrentRequest();
      $this->entityManager = $entity_manager;
    }

    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('request_stack'),
        $container->get('entity.manager'),
      );
    }

  function loginShib(){
    \Drupal::service('page_cache_kill_switch')->trigger();

    $uri = $this->request->getSchemeAndHttpHost() . $this->request->getBaseUrl();

    if($this->request->query->get('target')) {
      $uri = $this->request->query->get('target');
    }

    $netid = $this->request->server->get(\Drupal::config('uw_auth.settings')->get('username_field'));
    $email = $this->request->server->get(\Drupal::config('uw_auth.settings')->get('email_field'));

    if( $netid != ''
      && $email != ''
      && $this->request->query->get('shiblogin') == '1'
    ){
      if(\Drupal::config('uw_auth.settings')->get('force_uw_groups')){
        $NetIDGroups = new \Drupal\uw_groups\NetIDGroups();

        if(!$NetIDGroups->isNetIDInAnyActiveGroup($netid)){
          \Drupal::logger('uw_auth')->notice('UW Auth - Netid = '.$netid.' - Not in active group');
          throw new AccessDeniedHttpException();
        }
      }

      // Find the user
      $account_search = $this->entityManager->getStorage('user')->loadByProperties(array('name' => $netid));
      // $account = user_load_by_name($this->request->server->get(\Drupal::config('uw_auth.settings')->get('username_field')));

      $account = null;
      if(is_array($account_search)){
        $account = current($account_search);
      }

      // Create the user
      if(\Drupal::config('uw_auth.settings')->get('autocreate_accounts') && !$account){
        $account = \Drupal\user\Entity\User::create();
        $account->setPassword(str_shuffle(md5(microtime()*rand(15,99999)).md5(microtime()))); // Set a dummy password
        $account->enforceIsNew();
        $account->setEmail($email);
        $account->setUsername($netid);
        $account->activate();
        $account->save();
      }elseif(!$account){
        throw new AccessDeniedHttpException();
      }


      if($account){
        user_login_finalize($account);
      }else{
        \Drupal::logger('uw_auth')->notice('UW Auth - netid='.$netid.' - Account not found');
        throw new AccessDeniedHttpException();
      }
    }else{
      \Drupal::logger('uw_auth')->notice('UW Auth - netid='.$netid.' email='.$email.' - Missing auth variable');
      throw new AccessDeniedHttpException();
    }

    return array(
      '#theme' => 'redirect',
      '#title' => 'Auth redirect',
      '#hide_title' => true,
      '#page_title' => 'iSchool Events',
      '#target_url' => $uri,
      '#cache' => [
        'keys' => ['uw_auth'],
        'contexts' => ['user.roles', 'url.query_args:target', 'session.exists'],
        'max-age' => 1,
        'tags' => ['uw_auth'],
      ],
    );
  }

}
