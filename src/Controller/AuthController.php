<?php
/**
 * @file
 * Contains \Drupal\ischool_pages\Controller\ImpactStoriesController.
 */

namespace Drupal\uw_auth\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\image\Entity\ImageStyle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class AuthController extends ControllerBase {

    /** Dependency injection **/
    protected $request;

    public function __construct(RequestStack $request_stack) {
      // print_r($request_stack->getCurrentRequest()->request);
      $this->request = $request_stack->getCurrentRequest();
    }

    public static function create(ContainerInterface $container) {
      return new static(
        $container->get('request_stack')
      );
    }

  function loginShib(){
    $uri = $this->request->getSchemeAndHttpHost() . $this->request->getBaseUrl();

    if($this->request->query->get('target')) {
      $uri = $this->request->query->get('target');
    }
    $this->request->getSession()->save();

    // $user = \Drupal::currentUser();
    // print_r($user);die;
    // // print_r($user);
    // if($user){
    //   // user_login_finalize($user);
    // }

    // print_r($this->request->server->all());
    // die;

    // $response = new \Symfony\Component\HttpFoundation\RedirectResponse($uri);

    // \Drupal::service('kernel')->terminate($this->request, $response);
    // $response->send();
    return array(
      '#theme' => 'redirect',
      '#title' => 'Auth redirect',
      '#hide_title' => true,
      '#page_title' => 'iSchool Events',
      '#target_url' => $uri,
      '#cache' => [
        'keys' => ['special-key'],
        'contexts' => ['user.roles', 'url.query_args:target', 'session.exists'],
        'max-age' => 1,
        'tags' => ['special-tag'],
      ],
    );
  }

}
