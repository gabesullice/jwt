<?php

namespace Drupal\jwt;

interface JwtEncoderInterface {

  /**
   * Encodes a JWT object a signed JWT token.
   *
   * @param \Drupal\jwt\JwtInterface
   *   The JWT to encode.
   *
   * @return string
   *   The encoded JWT token.
   */
  public function encode(JwtInterface $jwt);

  /**
   * Returns the algorithm that will be used to encode a JWT.
   *
   * @return string
   *   An algorithm name.
   */
  public function getAlgorithm();

  /**
   * Set the signing algorithm to use when encoding.
   *
   * @param string $algorithm
   *   (optional) The algorithm to use. If no algorithm is provided, the
   *   configured default will be used.
   */
  public function setAlgorithm($algorithm = FALSE);

  /**
   * A list of signing algorithms supported by the system.
   *
   * @return string[]
   *   The list of algorithms supported.
   */
  public function signingAlgorithms();

  /**
   * The default signing algorithm.
   *
   * @return string
   *   The default algorithm.
   */
  public function defaultAlgorithm();

}
