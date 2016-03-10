<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_consumer\JWTAuthConsumerSubscriber.
 */

namespace Drupal\jwt_auth_consumer\EventSubscriber;

use Drupal\jwt\Authentication\Provider\JWTAuthEvent;
use Drupal\jwt\Authentication\Provider\JWTAuthEvents;

use Drupal\Core\Entity\EntityManagerInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class JWTAuthConsumerSubscriber.
 *
 * @package Drupal\jwt_auth_consumer
 */
class JWTAuthConsumerSubscriber implements EventSubscriberInterface {

  /**
   * A User Interface.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  private static $entityManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[JWTAuthEvents::VALIDATE][] = array('validate');
    $events[JWTAuthEvents::VALID][] = array('loadUser');

    return $events;
  }

  /**
   * Validates that a uid is present in the JWT.
   *
   * This validates the format of the JWT. It does NOT validate the uid is a
   * valid uid in the system.
   *
   * @param \Drupal\jwt\JWTAuthEvent $event
   *  A JWTAuth event.
   */
  public function validate(JWTAuthEvent $event) {
    $token = $event->getToken();
    if (!isset($token->uid)) {
      $event->invalidate("No uid was provided in the JWT payload.");
    }
  }

  /**
   * Load and set a Drupal user to be authentication based on the JWT's uid.
   *
   * @param \Drupal\jwt\JWTAuthEvent $event
   *  A JWTAuth event.
   */
  public function loadUser(JWTAuthEvent $event) {
    $token = $event->getToken();
    if (!$user = $this->entityManager->getStorage('user')->load($token->uid)) {
      // @todo: log error recording that no user by this uid was found.
      return;
    }
    $event->setUser($user);
  }

}
