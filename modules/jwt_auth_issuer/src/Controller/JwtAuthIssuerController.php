<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerController.
 */

namespace Drupal\jwt_auth_issuer\Controller;

use Drupal\jwt\JsonWebToken\JsonWebToken;
use Drupal\jwt\Transcoder\JwtTranscoderInterface;

use Drupal\Core\Controller\ControllerBase;
use Drupal\key\KeyRepositoryInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JwtAuthIssuerController.
 *
 * @package Drupal\jwt_auth_issuer\Controller
 */
class JwtAuthIssuerController extends ControllerBase {

  /**
   * The JWT transcoder service.
   *
   * @var Drupal\jwt\Transcoder\JwtTranscoderInterface
   */
  protected $transcoder;

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
   * @param \Drupal\jwt\Transcoder\JwtTranscoderInterface
   *  The JWT transcoder service.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepo
   *   The key module repository service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(JwtTranscoderInterface $transcoder, KeyRepositoryInterface $key_repo, EventDispatcherInterface $event_dispatcher) {
    $this->transcoder = $transcoder;
    $this->keyRepo = $key_repo;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $transcoder = $container->get('jwt.transcoder');
    $key_repo = $container->get('key.repository');

    $secret = $key_repo->getKey('jwt_key')->getKeyValue();
    $transcoder->setSecret($secret);

    return new static(
      $transcoder,
      $key_repo,
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
    $response = new \stdclass();
    $response->token = $this->generateToken();
    return new JsonResponse($response);
  }

  /**
   * Generates a new JWT.
   */
  protected function generateToken() {
    $token = new JsonWebToken();
    $event = new JwtAuthIssuerEvent($token);
    $this->eventDispatcher->dispatch(JwtAuthIssuerEvents::GENERATE, $event);
    $jwt = $event->getToken();

    return $this->transcoder->encode($jwt);
  }

}
