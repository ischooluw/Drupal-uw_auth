<?php

namespace Drupal\uw_auth\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects post-login requests before page rendering begins.
 */
class LoginRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Redirects requests carrying legacy shiblogin or target query parameters.
   */
  public function onKernelRequest(RequestEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    if (!$request->query->get('target') && !$request->query->get('shiblogin')) {
      return;
    }

    // The dedicated login route already returns the right redirect response.
    if ($request->getPathInfo() === '/uwlogin') {
      return;
    }

    $redirect = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
    $target = $request->query->get('target');

    if (is_string($target) && filter_var($target, FILTER_VALIDATE_URL)) {
      $parts = parse_url($target);
      $redirect = $parts['path'] ?? '/';
      $redirect = $redirect === '' ? '/' : $redirect;

      if (isset($parts['query'])) {
        $redirect .= '?' . $parts['query'];
      }
      if (isset($parts['fragment'])) {
        $redirect .= '#' . $parts['fragment'];
      }
    }

    if ($request->hasSession()) {
      $request->getSession()->save();
    }

    $event->setResponse(new RedirectResponse($redirect));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after authentication, before route authentication filtering.
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 100];
    return $events;
  }

}
