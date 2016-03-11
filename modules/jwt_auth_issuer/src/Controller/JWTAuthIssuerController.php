<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerController.
 */

namespace Drupal\jwt_auth_issuer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\key\KeyRepositoryInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Firebase\JWT\JWT;

/**
 * Class JWTAuthIssuerController.
 *
 * @package Drupal\jwt_auth_issuer\Controller
 */
class JWTAuthIssuerController extends ControllerBase {

  /**
   * Firebase\JWT\JWT definition.
   *
   * @var Firebase\JWT\JWT
   */
  protected $phpJWT;

  /**
   * The key repository service.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepo;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   * 
   * @param \Firebase\JWT\JWT $php_jwt
   *  The JWT service.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepo
   *   The key module repository service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(JWT $php_jwt, KeyRepositoryInterface $key_repo, EventDispatcherInterface $event_dispatcher) {
    $this->phpJWT = $php_jwt;
    $this->keyRepo = $key_repo;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jwt.firebase.php-jwt'),
      $container->get('key.repository'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function tokenResponse() {
    $response->token = $this->generateToken();
    return new JsonResponse($response);
  }

  /**
   * Generates a new JWT.
   */
  private function generateToken() {
    $token = new \stdclass();
    $event = new JWTAuthIssuerEvent($token);
    $this->eventDispatcher->dispatch(JWTAuthIssuerEvents::GENERATE, $event);

    $secret = $this->keyRepo->getKey('jwt_key')->getKeyValue();
    $jwt = $event->getToken();
    return $this->phpJWT->encode($jwt, $secret);
  }

}
