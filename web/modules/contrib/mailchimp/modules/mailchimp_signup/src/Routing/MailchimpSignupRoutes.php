<?php

namespace Drupal\mailchimp_signup\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes for Mailchimp signup forms rendered as pages.
 */
class MailchimpSignupRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];

    $signups = mailchimp_signup_load_multiple();

    /** @var \Drupal\mailchimp_signup\Entity\MailchimpSignup $signup */
    foreach ($signups as $signup) {
      if ((intval($signup->mode) == MAILCHIMP_SIGNUP_PAGE) || (intval($signup->mode) == MAILCHIMP_SIGNUP_BOTH)) {
        $routes['mailchimp_signup.' . $signup->id] = new Route(
          // Route Path.
          '/' . $signup->settings['path'],
          // Route defaults.
          [
            '_controller' => '\Drupal\mailchimp_signup\Controller\MailchimpSignupController::page',
            '_title' => $signup->title,
            'signup_id' => $signup->id,
          ],
          // Route requirements.
          [
            '_permission'  => 'access mailchimp signup pages',
          ]
        );
      }
    }

    return $routes;
  }

}
