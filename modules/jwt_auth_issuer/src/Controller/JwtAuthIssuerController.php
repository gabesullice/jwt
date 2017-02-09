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
   * The secret to be used for the JWT.
   *
   * @var string
   */
  protected $secret;

  /**
   * {@inheritdoc}
   * 
   * @param \Drupal\jwt\Transcoder\JwtTranscoderInterface
   *  The JWT transcoder service.
   * @param string $secret
   *   The secret to be used for the JWT.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepo
   *   The key module repository service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(JwtTranscoderInterface $transcoder, $secret, KeyRepositoryInterface $key_repo, EventDispatcherInterface $event_dispatcher) {
    $this->transcoder = $transcoder;
    $this->keyRepo = $key_repo;
    $this->eventDispatcher = $event_dispatcher;
    $this->secret = $secret;
    $this->transcoder->setSecret($this->secret);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $transcoder = $container->get('jwt.transcoder');
    $key_repo = $container->get('key.repository');
    $key_name = $container->get('config.factory')->get('jwt.config')->get('key_id');
    $key = $key_repo->getKey($key_name);
    $secret = $key ? $key->getKeyValue() : NULL;

    return new static(
      $transcoder,
      $secret,
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

    if($this->secret === NULL) {
      $response->error = "Please set a key in the JWT admin page.";
      return new JsonResponse($response, 500);
    }

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
