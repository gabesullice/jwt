<?php

namespace Drupal\jwt_auth_refresh\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\jwt\Authentication\Provider\JwtAuth;
use Drupal\jwt\Transcoder\JwtDecodeException;
use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt_auth_issuer\Controller\JwtAuthIssuerController;
use Drupal\jwt_auth_refresh\JwtRefreshTokensInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
   * JWT Transcoder.
   *
   * @var \Drupal\jwt\Transcoder\JwtTranscoderInterface
   */
  protected $jwtTranscoder;

  /**
   * Current request.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @inheritDoc
   */
  public function __construct(JwtAuth $auth, JwtRefreshTokensInterface $refreshTokens, AccountSwitcherInterface $accountSwitcher, FloodInterface $flood, JwtTranscoderInterface $jwtTranscoder, RequestStack $requestStack) {
    parent::__construct($auth, $refreshTokens);
    $this->accountSwitcher = $accountSwitcher;
    $this->flood = $flood;
    $this->jwtTranscoder = $jwtTranscoder;
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jwt.authentication.jwt'),
      $container->get('jwt_auth_refresh.tokens'),
      $container->get('account_switcher'),
      $container->get('flood'),
      $container->get('jwt.transcoder'),
      $container->get('request_stack')
    );
  }

  /**
   * Enforces flood control for the current login request, by IP.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
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
    $owner = $this->getToken($request)->getOwner();
    // @todo - Better approach than switching?
    $this->accountSwitcher->switchTo($owner);
    $response = $this->tokenResponse();
    $this->accountSwitcher->switchBack();
    return $response;
  }

  /**
   * Retrieve the refresh token from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Drupal\jwt_auth_refresh\JwtRefreshTokenInterface|null
   */
  protected function getToken(Request $request) {
    $json = json_decode($request->getContent());
    try {
      $jti = $this->jwtTranscoder->decode($json->refresh_token)->getClaim('jti');
    }
    catch (JwtDecodeException $e) {
      return NULL;
    }
    $tokens = $this->entityTypeManager()->getStorage('jwt_refresh_token')->loadByProperties([
      'jti' => $jti,
    ]);
    if ($tokens) {
      return reset($tokens);
    }
    return NULL;
  }

  /**
   * Access checker.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function access() {
    // We can't type-hint $request
    // @see https://www.drupal.org/node/2786941
    $result = AccessResult::allowed();
    try {
      $this->floodControl();
    }
    catch (\Exception $e) {
      $result = AccessResult::forbidden($e->getMessage());
    }
    if ($token = $this->getToken($this->currentRequest)) {
      $owner = $token->getOwner();
      if (!$owner->isActive()) {
        $result = AccessResult::forbidden('Account not active.');
      }

    }
    else {
      $result = AccessResult::forbidden('No token provided.');
    }
    if ($result->isForbidden()) {
      $this->flood->register('jwt_auth_refresh.failed_refresh_ip', $this->config('user.flood')->get('ip_window'));
    }
    return $result;
  }

}
