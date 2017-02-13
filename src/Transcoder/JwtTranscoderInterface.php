<?php

namespace Drupal\jwt\Transcoder;

use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;

/**
 * Interface JwtTranscoderInterface.
 *
 * @package Drupal\jwt
 */
interface JwtTranscoderInterface {

  /**
   * Gets a validated JsonWebToken from an encoded JWT.
   *
   * @param string $jwt
   *   The encoded JWT.
   *
   * @return \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   *   Validated JWT.
   */
  public function decode($jwt);

  /**
   * Encodes a JsonWebToken.
   *
   * @param \Drupal\jwt\JsonWebToken\JsonWebTokenInterface $jwt
   *   A JWT.
   * @param array $options
   *   Options, optional.
   *
   * @return string
   *   The encoded JWT.
   */
  public function encode(JsonWebTokenInterface $jwt, array $options = []);

  /**
   * Setter for the JWT secret.
   *
   * @param string $secret
   *   The secret for the JWT.
   */
  public function setSecret($secret);

}
