<?php

/**
 * @file
 * Contains \Drupal\jwt\Validator\JwtValidator.
 */

namespace Drupal\jwt\Validator;

use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;
use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt\Transcoder\JwtDecodeException;
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
   * The current JsonWebToken.
   */
  protected $jwt;

  /**
   * Constructor.
   */
  public function __construct(RequestStack $request_stack, JwtTranscoderInterface $jwt_transcoder) {
    $this->requestStack = $request_stack;
    $this->jwtTranscoder = $jwt_transcoder;
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
   * @throws JwtInvalidException
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
    }

    return $jwt;
  }

}
