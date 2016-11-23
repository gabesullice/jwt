<?php

namespace Drupal\jwt;

interface JwtInterface {

  /**
   * Returns the JWT payload.
   *
   * @return \Symfony\Component\HttpFoundation\ParameterBag
   */
  public function getPayload();

  /**
   * Encodes JWT object into a signed JWT string.
   *
   * @return string
   *   The encoded JWT string.
   */
  public function encode();

}
