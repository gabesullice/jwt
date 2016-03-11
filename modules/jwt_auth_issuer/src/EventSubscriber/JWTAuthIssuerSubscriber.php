<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\JWTAuthIssuerSubscriber.
 */

namespace Drupal\jwt_auth_issuer\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvent;
use Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class JWTAuthIssuerSubscriber.
 *
 * @package Drupal\jwt_auth_issuer
 */
class JWTAuthIssuerSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *  The current user.
   */
  public function __construct(AccountInterface $user) {
    $this->currentUser = $user;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[JWTAuthIssuerEvents::GENERATE][] = array('setStandardClaims', 100);
    $events[JWTAuthIssuerEvents::GENERATE][] = array('setDrupalClaims', 99);
    return $events;
  }

  /**
   * Sets the standard claims set for a JWT.
   *
   * @param \Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvent $event
   */
  public function setStandardClaims($event) {
    $event->addClaim('iat', time());
    // @todo: make these more configurable.
    $event->addClaim('exp', strtotime('+15 minutes'));
  }

  /**
   * Sets claims for a Drupal consumer on the JWT.
   *
   * @param \Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvent $event
   */
  public function setDrupalClaims($event) {
    $event->addClaim(
      array(
        0 => 'drupal',
        1 => 'uid'
      ),
      $this->currentUser->id()
    );
  }

}
