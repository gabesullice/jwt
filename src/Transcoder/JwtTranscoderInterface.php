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
   *
   * @throws \Drupal\jwt\Transcoder\JwtDecodeException
   */
  public function decode($jwt);

  /**
   * Encodes a JsonWebToken.
   *
   * @param \Drupal\jwt\JsonWebToken\JsonWebTokenInterface $jwt
   *   A JWT.
   *
   * @return string
   *   The encoded JWT.
   */
  public function encode(JsonWebTokenInterface $jwt);

  /**
   * Sets the secret that is used for a symmetric algorithm signature.
   *
   * The secret is only used when a symmetric algorithm is selected. Currently
   * the symmetric algorithms supported are:
   *   * HS256
   *   * HS384
   *   * HS512
   * The secret is used for both signature creation and verification.
   *
   * @param string $secret
   *   The secret for the JWT.
   */
  public function setSecret($secret);

  /**
   * Sets the algorithm to be used for the JWT.
   *
   * @param string $algorithm
   *   This can be any of the array keys returned by the getAlgorithmOptions
   *   function.
   *
   * @see getAlgorithmOptions()
   */
  public function setAlgorithm($algorithm);

  /**
   * Sets the private key used to create signatures for an asymmetric algorithm.
   *
   * This key is only used when an asymmetric algorithm is selected. Currently
   * supported asymmetric algorithms are:
   *   * RS256
   *
   * @param string $private_key
   *   A PEM encoded private key.
   * @param bool $derive_public_key
   *   (Optional) Derive the public key from the private key. Defaults to true.
   *
   * @return bool
   *   Function does some validation of the key. Returns TRUE on success.
   */
  public function setPrivateKey($private_key, $derive_public_key = TRUE);

  /**
   * Sets the public key used to verify signatures for an asymmetric algorithm.
   *
   * This key is only used when an asymmetric algorithm is selected. Currently
   * supported asymmetric algorithms are:
   *   * RS256
   *
   * @param string $public_key
   *   A PEM encoded public key.
   *
   * @return mixed
   *   Function does some validation of the key. Returns TRUE on success.
   */
  public function setPublicKey($public_key);

  /**
   * Return the type of algorithm selected.
   *
   * @param string $algorithm
   *   The algorithm.
   *
   * @return string
   *   The algorithm type. Returns NULL if algorithm not found.
   */
  public static function getAlgorithmType($algorithm);

  /**
   * Gets a list of algorithms supported by this transcoder.
   *
   * @return array
   *   An array of options formatted for a select list.
   */
  public static function getAlgorithmOptions();

}
