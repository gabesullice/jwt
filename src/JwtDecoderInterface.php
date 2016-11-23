<?php

namespace Drupal\jwt;

interface JwtDecoderInterface {

  /**
   * Decodes a JSON Web Token into an object.
   *
   * @param string $encoded
   *   The encoded JWT.
   *
   * @return \Drupal\jwt\JwtInterface
   *   The decoded JWT object.
   *
   * @thows \Drupal\jwt\JwtEncoderException
   *   Describes any decoding errors.
   */
  public function decode($encoded);

  /**
   * A list of verification algorithms supported by the system.
   *
   * @return string[]
   *   The list of algorithms supported.
   */
  public function supportedAlgorithms();

}
