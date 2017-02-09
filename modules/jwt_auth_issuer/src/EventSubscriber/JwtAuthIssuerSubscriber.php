<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\JwtAuthIssuerSubscriber.
 */

namespace Drupal\jwt_auth_issuer\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerEvent;
use Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class JwtAuthIssuerSubscriber.
 *
 * @package Drupal\jwt_auth_issuer
 */
class JwtAuthIssuerSubscriber implements EventSubscriberInterface {

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
    $events[JwtAuthIssuerEvents::GENERATE][] = array('setStandardClaims', 100);
    $events[JwtAuthIssuerEvents::GENERATE][] = array('setDrupalClaims', 99);
    return $events;
  }

  /**
   * Sets the standard claims set for a JWT.
   *
   * @param \Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerEvent $event
   */
  public function setStandardClaims(JwtAuthIssuerEvent $event) {
    $event->addClaim('iat', time());
    // @todo: make these more configurable.
    $event->addClaim('exp', strtotime('+1 hour'));
  }

  /**
   * Sets claims for a Drupal consumer on the JWT.
   *
   * @param \Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerEvent $event
   */
  public function setDrupalClaims(JwtAuthIssuerEvent $event) {
    $event->addClaim(
      array(
        0 => 'drupal',
        1 => 'uid'
      ),
      $this->currentUser->id()
    );
  }

}
