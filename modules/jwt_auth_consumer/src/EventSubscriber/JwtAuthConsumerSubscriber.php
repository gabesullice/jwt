<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_consumer\JwtAuthConsumerSubscriber.
 */

namespace Drupal\jwt_auth_consumer\EventSubscriber;

use Drupal\jwt\Authentication\Provider\JwtAuthEvent;
use Drupal\jwt\Authentication\Provider\JwtAuthEvents;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class JwtAuthConsumerSubscriber.
 *
 * @package Drupal\jwt_auth_consumer
 */
class JwtAuthConsumerSubscriber implements EventSubscriberInterface {

  /**
   * A User Interface.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

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
    $events[JwtAuthEvents::VALIDATE][] = array('validate');
    $events[JwtAuthEvents::VALID][] = array('loadUser');

    return $events;
  }

  /**
   * Validates that a uid is present in the JWT.
   *
   * This validates the format of the JWT. It does NOT validate the uid is a
   * valid uid in the system.
   *
   * @param \Drupal\jwt\Authentication\Provider\JwtAuthEvent $event
   *  A JwtAuth event.
   */
  public function validate(JwtAuthEvent $event) {
    $token = $event->getToken();
    if (!isset($token->drupal->uid)) {
      $event->invalidate("No Drupal uid was provided in the JWT payload.");
    }
  }

  /**
   * Load and set a Drupal user to be authentication based on the JWT's uid.
   *
   * @param \Drupal\jwt\Authentication\Provider\JwtAuthEvent $event
   *  A JwtAuth event.
   */
  public function loadUser(JwtAuthEvent $event) {
    $token = $event->getToken();
    $user_storage = $this->entityManager->getStorage('user');
    $uid = $token->getClaim(array('drupal', 'uid'));
    $user = $user_storage->load($uid);
    if (!$user) {
      // @todo: log notice recording that no user by this uid was found.
      return;
    }
    $event->setUser($user);
  }

}
