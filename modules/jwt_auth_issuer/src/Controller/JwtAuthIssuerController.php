<?php

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
   * @var \Drupal\jwt\Transcoder\JwtTranscoderInterface
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
   * @param \Drupal\jwt\Transcoder\JwtTranscoderInterface $transcoder
   *   The JWT transcoder service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(JwtTranscoderInterface $transcoder, EventDispatcherInterface $event_dispatcher) {
    $this->transcoder = $transcoder;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $transcoder = $container->get('jwt.transcoder');

    return new static(
      $transcoder,
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
    $response = new \stdClass();

    $token = $this->generateToken();
    if ($token === FALSE) {
      $response->error = "Error. Please set a key in the JWT admin page.";
      return new JsonResponse($response, 500);
    }

    $response->token = $token;
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
