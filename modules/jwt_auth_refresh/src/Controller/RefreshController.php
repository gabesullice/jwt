<?php

namespace Drupal\jwt_auth_refresh\Controller;

use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\jwt\Authentication\Provider\JwtAuth;
use Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerController;
use Drupal\jwt_auth_refresh\JwtRefreshTokensInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RefreshController extends JwtAuthIssuerController {

  /**
   * Account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * @inheritDoc
   */
  public function __construct(JwtAuth $auth, JwtRefreshTokensInterface $refreshTokens, AccountSwitcherInterface $accountSwitcher) {
    parent::__construct($auth, $refreshTokens);
    $this->accountSwitcher = $accountSwitcher;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jwt.authentication.jwt'),
      $container->get('jwt_auth_refresh.tokens'),
      $container->get('account_switcher')
    );
  }

  /**
   * Refresh controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function refresh(Request $request) {
    $response = new JsonResponse([], 404);
    try {
      $json = \GuzzleHttp\json_decode($request->getContent());
    }
    catch (\Exception $e) {
      return $response;
    }
    if (!empty($json->refresh_token)) {
      $tokens = $this->entityTypeManager()->getStorage('jwt_refresh_token')->loadByProperties([
        'token' => $json->refresh_token,
        'status' => 1,
      ]);
      if (!$tokens) {
        return $response;
      }
      /** @var \Drupal\jwt_auth_refresh\JwtRefreshTokenInterface $token */
      $token = reset($tokens);
      // @todo - Better approach than switching?
      $this->accountSwitcher->switchTo($token->getOwner());
      $response = $this->tokenResponse();
      $this->accountSwitcher->switchBack();
    }
    return $response;
  }

}
