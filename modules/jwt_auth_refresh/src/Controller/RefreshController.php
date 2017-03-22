<?php

namespace Drupal\jwt_auth_refresh\Controller;

use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\jwt\Authentication\Provider\JwtAuth;
use Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerController;
use Drupal\jwt_auth_refresh\JwtRefreshTokensInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RefreshController extends JwtAuthIssuerController {

  /**
   * Account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * Flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * @inheritDoc
   */
  public function __construct(JwtAuth $auth, JwtRefreshTokensInterface $refreshTokens, AccountSwitcherInterface $accountSwitcher, FloodInterface $flood) {
    parent::__construct($auth, $refreshTokens);
    $this->accountSwitcher = $accountSwitcher;
    $this->flood = $flood;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jwt.authentication.jwt'),
      $container->get('jwt_auth_refresh.tokens'),
      $container->get('account_switcher'),
      $container->get('flood')
    );
  }

  /**
   * Enforces flood control for the current login request, by IP.
   */
  protected function floodControl() {
    $flood_config = $this->config('user.flood');
    if (!$this->flood->isAllowed('jwt_auth_refresh.failed_refresh_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
      throw new AccessDeniedHttpException('Access is blocked because of IP based flood prevention.', NULL, Response::HTTP_TOO_MANY_REQUESTS);
    }
  }

  /**
   * Refresh controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function refresh(Request $request) {
    $this->floodControl();
    $response = new JsonResponse([], 404);
    $json = json_decode($request->getContent());
    if (!empty($json->refresh_token)) {
      $tokens = $this->entityTypeManager()->getStorage('jwt_refresh_token')->loadByProperties([
        'token' => $json->refresh_token,
        'status' => 1,
      ]);
      if ($tokens) {
        /** @var \Drupal\jwt_auth_refresh\JwtRefreshTokenInterface $token */
        $token = reset($tokens);
        $owner = $token->getOwner();
        if (!$owner->isActive()) {
          throw new BadRequestHttpException('The user has not been activated or is blocked.');
        }
        // @todo - Better approach than switching?
        $this->accountSwitcher->switchTo($owner);
        $response = $this->tokenResponse();
        $this->accountSwitcher->switchBack();
      }
    }
    if ($response->getStatusCode() != 200) {
      $this->flood->register('jwt_auth_refresh.failed_refresh_ip', $this->config('user.flood')->get('ip_window'));
    }
    return $response;
  }

}
