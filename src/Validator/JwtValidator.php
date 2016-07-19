<?php

/**
 * @file
 * Contains \Drupal\jwt\Validator\JwtValidator.
 */

namespace Drupal\jwt\Validator;

use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt\Transcoder\JwtDecodeException;

use Drupal\key\KeyRepositoryInterface;

use Drupal\Core\Config\ConfigFactoryInterface;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class JwtValidator.
 *
 * @package Drupal\jwt
 */
class JwtValidator implements JwtValidatorInterface {

  /**
   * The related request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The JWT transcoder service.
   *
   * @var \Drupal\jwt\Transcoder\JwtTranscoderInterface
   */
  protected $jwtTranscoder;

  /**
   * The key manager.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepo;

  /**
   * The current JsonWebToken.
   */
  protected $jwt;

  /**
   * Constructor.
   */
  public function __construct(RequestStack $request_stack, JwtTranscoderInterface $jwt_transcoder, KeyRepositoryInterface $key_repo, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $request_stack;
    $this->jwtTranscoder = $jwt_transcoder;
    $this->keyRepo = $key_repo;

    $key_id = $config_factory->get('jwt.config')->get('key_id');
    if (isset($key_id)) {
      $key = $key_repo->getKey($key_id);

      if (!is_null($key)) {
        $secret = $key->getKeyValue();
        $this->jwtTranscoder->setSecret($secret);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getJwt($reset = FALSE) {
    if (!$this->jwt || $reset) {
      $request = $this->requestStack->getCurrentRequest();
      $this->jwt = $this->getJwtFromRequest($request);
    }
    return $this->jwt;
  }

  /**
   * {@inheritdoc}
   */
  public function setJwt(JsonWebTokenInterface $jwt) {
    $this->jwt = $jwt;
  }

  /**
   * {@inheritdoc}
   */
  public function assertClaim($claim, $value) {
    if (!$jwt = $this->getJwt()) {
      return FALSE;
    }
    return ($jwt->getClaim($claim) === $value);
  }

  /**
   * Gets a JsonWebToken from the current request.
   *
   * @return mixed
   *  JsonWebToken if on request, false if not.
   */
  protected function getJwtFromRequest(Request $request) {
    $auth_header = $request->headers->get('Authorization');
    $matches = array();
    if (!$hasJWT = preg_match('/^Bearer (.*)/', $auth_header, $matches)) {
      return FALSE;
    }
    try {
      $jwt = $this->jwtTranscoder->decode($matches[1]);
    }
    catch (JwtDecodeException $e) {
      throw new JwtInvalidException($e->getMessage(), JwtInvalidException::DECODE_ERROR, $e);
      return null;
    }

    return $jwt;
  }

}
