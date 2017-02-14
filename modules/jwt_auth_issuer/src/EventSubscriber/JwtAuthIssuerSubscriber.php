<?php

namespace Drupal\jwt_auth_issuer\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\jwt\Authentication\Event\JwtAuthEvents;
use Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent;
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
   *   The current user.
   */
  public function __construct(AccountInterface $user) {
    $this->currentUser = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[JwtAuthEvents::GENERATE][] = ['setStandardClaims', 100];
    $events[JwtAuthEvents::GENERATE][] = ['setDrupalClaims', 99];
    return $events;
  }

  /**
   * Sets the standard claims set for a JWT.
   *
   * @param \Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent $event
   *   The event.
   */
  public function setStandardClaims(JwtAuthGenerateEvent $event) {
    $event->addClaim('iat', time());
    // @todo: make these more configurable.
    $event->addClaim('exp', strtotime('+1 hour'));
  }

  /**
   * Sets claims for a Drupal consumer on the JWT.
   *
   * @param \Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent $event
   *   The event.
   */
  public function setDrupalClaims(JwtAuthGenerateEvent $event) {
    $event->addClaim(
      ['drupal', 'uid'],
      $this->currentUser->id()
    );
  }

}
