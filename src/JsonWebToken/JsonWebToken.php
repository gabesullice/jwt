<?php

/**
 * @file
 * Contains \Drupal\jwt\JsonWebToken\JsonWebToken.
 */

namespace Drupal\jwt\JsonWebToken;

/**
 * Class JsonWebToken.
 *
 * @package Drupal\jwt\JsonWebToken
 */
class JsonWebToken implements JsonWebTokenInterface {

  /**
   * Internal representation of the token.
   *
   * @var string
   */
  protected $payload;

  public function __construct($jwt = null) {
    $jwt = (is_null($jwt)) ? new \stdClass() : $jwt;
    $this->payload = $jwt;
  }

  /**
   * {@inheritdoc}
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * {@inheritdoc}
   */
  public function getClaim($claim) {
    $payload = $this->payload;
    return $this->_getClaim($payload, $claim);
  }

  /**
   * {@inheritdoc}
   */
  public function setClaim($claim, $value) {
    $payload = $this->payload;
    $this->_setClaim($payload, $claim, $value);
    $this->payload = $payload;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetClaim($claim) {
    $payload = $this->payload;
    $this->_unsetClaim($payload, $claim);
    $this->payload = $payload;
  }

  /**
   * Traverses the JWT payload to the given claim and returns its value.
   *
   * @param object $payload
   *  A reference to the JWT payload.
   * @param mixed $claim
   *  Either a string or indexed array of strings representing the claim or
   *  nested claim to be set.
   * @return mixed
   */
  protected function _getClaim(&$payload, $claim) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (!isset($payload->$current_claim)) {
      return null;
    }

    if (is_array($claim) && count($claim) > 0) {
      return $this->_getClaim($payload->$current_claim, $claim);
    }
    else {
      return $payload->$current_claim;
    }
  }

  /**
   * Traverses the JWT payload to the given claim and sets a value.
   *
   * @param object $payload
   *  A reference to the JWT payload.
   * @param mixed $claim
   *  Either a string or indexed array of strings representing the claim or
   *  nested claim to be set.
   * @param mixed $value
   *  The value to set for the given claim.
   */
  protected function _setClaim(&$payload, $claim, $value) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (is_array($claim) && count($claim) > 0) {
      if (!isset($payload->$current_claim)) {
        $payload->$current_claim = new \stdClass();
      }

      $this->_setClaim($payload->$current_claim, $claim, $value);
    }
    else {
      $payload->$current_claim = $value;
    }
  }

  /**
   * Traverses the JWT payload to the given claim and unsets its value.
   *
   * @param object $payload
   *  A reference to the JWT payload.
   * @param mixed $claim
   *  Either a string or indexed array of strings representing the claim or
   *  nested claim to be set.
   */
  protected function _unsetClaim(&$payload, $claim) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (!isset($payload->$current_claim)) {
      return;
    }

    if (is_array($claim) && count($claim) > 0) {
      $this->_unsetClaim($payload->$current_claim, $claim);
    }
    else {
      unset($payload->$current_claim);
    }
  }

}
