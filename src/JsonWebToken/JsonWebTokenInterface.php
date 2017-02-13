<?php

namespace Drupal\jwt\JsonWebToken;

/**
 * Interface JsonWebTokenInterface.
 *
 * @package Drupal\jwt\JsonWebToken
 */
interface JsonWebTokenInterface {

  /**
   * Gets the unencoded payload as an object.
   *
   * @return \stdclass
   *   The unencoded payload.
   */
  public function getPayload();

  /**
   * Retrieve a claim from the JWT payload.
   *
   * @param mixed $claim
   *   Either a string or indexed array of strings (if nested) representing the
   *   claim to retrieve. If an indexed array is passed, it will be used to
   *   traverse the JWT where the 0th element is the topmost claim.
   *
   * @returns mixed The contents of the claim.
   */
  public function getClaim($claim);

  /**
   * Add or update the given claim with the given value.
   *
   * @param mixed $claim
   *   Either a string or indexed array of strings representing the claim or
   *   nested claim to be set.
   * @param mixed $value
   *   A serializable value to set the given claim to on the JWT.
   */
  public function setClaim($claim, $value);

  /**
   * Remove a claim from the JWT payload.
   *
   * @param mixed $claim
   *   Either a string or indexed array of strings.
   *
   * @See Drupal\jwt\JsonWebTokenInterface::getClaim().
   */
  public function unsetClaim($claim);

}
