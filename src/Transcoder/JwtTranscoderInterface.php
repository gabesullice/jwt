<?php

/**
 * @file
 * Contains \Drupal\jwt\Transcoder\JwtTranscoderInterface.
 */

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
   */
  public function decode($jwt);

  /**
   * Encodes a JsonWebToken.
   */
  public function encode(JsonWebTokenInterface $jwt, $options);

}
