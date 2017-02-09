<?php

/**
 * @file
 * Contains \Drupal\jwt\Authentication\Provider\JwtAuth.
 */

namespace Drupal\jwt\Authentication\Provider;

use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;
use Drupal\jwt\Validator\JwtInvalidException;
use Drupal\jwt\Validator\JwtValidatorInterface;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * JWT Authentication Provider.
 */
class JwtAuth implements AuthenticationProviderInterface {

  /**
   * The user auth service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The JWT Validator service.
   *
   * @var \Drupal\jwt\Validator\JwtValidatorInterface
   */
  protected $validator;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The user authentication service.
   * @param \Drupal\jwt\JwtValidatorInterface
   *   The jwt validator service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, JwtValidatorInterface $validator, EventDispatcherInterface $event_dispatcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->validator = $validator;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
	public function applies(Request $request) {
    $auth = $request->headers->get('Authorization');
		return preg_match('/^Bearer .+/', $auth);
	}

  /**
   * {@inheritdoc}
   */
	public function authenticate(Request $request) {
    try {
      $jwt = $this->validator->getJwt();
    } catch (JwtInvalidException $e) {
      throw new AccessDeniedHttpException($e->getMessage(), $e);
    }

    if (!$user = $this->getUser($jwt)) {
      throw new AccessDeniedHttpException('Unable to load user from provided JWT.');
    }

    return $user;
	}

  /**
   * Allow the system to interpret token and provide a user id.
   *
   * @param \Drupal\jwt\JsonWebToken\JsonWebTokenInterface $jwt
   *
   * @return mixed $uid
   *  A loaded user object.
   */
  private function getUser(JsonWebTokenInterface $jwt) {
    $event = new JwtAuthEvent($jwt);
    $this->eventDispatcher->dispatch(JwtAuthEvents::VALID, $event);
    return $event->getUser();
  }

}
