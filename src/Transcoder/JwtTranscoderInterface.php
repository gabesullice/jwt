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
   *
   * @returns \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   */
  public function decode($jwt);

  /**
   * Encodes a JsonWebToken.
   *
   * @param $jwt \Drupal\jwt\JsonWebToken\JsonWebTokenInterface A JWT
   * @param $options array Options, optional.
   *
   * @returns string The encoded JWT.
   */
  public function encode(JsonWebTokenInterface $jwt, $options = []);

}
