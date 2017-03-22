<?php

namespace Drupal\jwt_auth_issuer\Controller;

use Drupal\jwt\Authentication\Provider\JwtAuth;
use Drupal\Core\Controller\ControllerBase;
use Drupal\jwt_auth_refresh\JwtRefreshTokensInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JwtAuthIssuerController.
 *
 * @package Drupal\jwt_auth_issuer\Controller
 */
class JwtAuthIssuerController extends ControllerBase {

  /**
   * The JWT Auth Service.
   *
   * @var \Drupal\jwt\Authentication\Provider\JwtAuth
   */
  protected $auth;

  /**
   * Refresh token.
   *
   * @var \Drupal\jwt_auth_refresh\JwtRefreshTokensInterface
   */
  protected $refreshTokens;

  /**
   * Constructor.
   */
  public function __construct(JwtAuth $auth, JwtRefreshTokensInterface $refreshTokens) {
    $this->auth = $auth;
    $this->refreshTokens = $refreshTokens;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jwt.authentication.jwt'),
      $container->get('jwt_auth_refresh.tokens')
    );
  }

  /**
   * Generate.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse JSON response with token.
   */
  public function tokenResponse() {
    $response = new \stdClass();
    $token = $this->auth->generateToken();
    if ($token === FALSE) {
      $response->error = "Error. Please set a key in the JWT admin page.";
      return new JsonResponse($response, 500);
    }
    $response->token = $token;
    if ($this->moduleHandler()->moduleExists('jwt_auth_refresh')) {
      $response->refresh_token = $this->refreshTokens->retrieveForUser($this->currentUser())->getToken();
    }
    return new JsonResponse($response);
  }

}
