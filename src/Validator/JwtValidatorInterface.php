<?php

/**
 * @file
 * Contains \Drupal\jwt\Validator\JwtValidatorInterface.
 */

namespace Drupal\jwt\Validator;

/**
 * Interface JwtValidatorInterface.
 *
 * @package Drupal\jwt
 */
interface JwtValidatorInterface {

  /**
   * Returns a JsonWebToken.
   *
   * @return \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   */
  public function getJwt();

  /**
   * Asserts the value of a claim.
   *
   * This asserts that a given claim has the given value. Nested claims can be
   * accessed by passing an array of claims with which to traverse the JWT to
   * the desired claim value.
   *
   * @param mixed $claim
   *  The claim to retrieve.
   *
   * @param mixed $grant
   *  The claim grant to validate.
   *
   * @return bool
   *  Whether the JWT's claim contains the given grant.
   */
  public function assertClaim($claim, $grant);

}
