<?php

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

  /**
   * JsonWebToken constructor.
   *
   * @param object $jwt
   *   The Object to turn into a JWT.
   */
  public function __construct($jwt = NULL) {
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
    return $this->internalGetClaim($payload, $claim);
  }

  /**
   * {@inheritdoc}
   */
  public function setClaim($claim, $value) {
    $payload = $this->payload;
    $this->internalSetClaim($payload, $claim, $value);
    $this->payload = $payload;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetClaim($claim) {
    $payload = $this->payload;
    $this->internalUnsetClaim($payload, $claim);
    $this->payload = $payload;
  }

  /**
   * Traverses the JWT payload to the given claim and returns its value.
   *
   * @param object $payload
   *   A reference to the JWT payload.
   * @param mixed $claim
   *   Either a string or indexed array of strings representing the claim or
   *   nested claim to be set.
   *
   * @return mixed
   *   The claims value.
   */
  protected function internalGetClaim(&$payload, $claim) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (!isset($payload->$current_claim)) {
      return NULL;
    }

    if (is_array($claim) && count($claim) > 0) {
      return $this->internalGetClaim($payload->$current_claim, $claim);
    }
    else {
      return $payload->$current_claim;
    }
  }

  /**
   * Traverses the JWT payload to the given claim and sets a value.
   *
   * @param object $payload
   *   A reference to the JWT payload.
   * @param mixed $claim
   *   Either a string or indexed array of strings representing the claim or
   *   nested claim to be set.
   * @param mixed $value
   *   The value to set for the given claim.
   */
  protected function internalSetClaim(&$payload, $claim, $value) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (is_array($claim) && count($claim) > 0) {
      if (!isset($payload->$current_claim)) {
        $payload->$current_claim = new \stdClass();
      }

      $this->internalSetClaim($payload->$current_claim, $claim, $value);
    }
    else {
      $payload->$current_claim = $value;
    }
  }

  /**
   * Traverses the JWT payload to the given claim and unset its value.
   *
   * @param object $payload
   *   A reference to the JWT payload.
   * @param mixed $claim
   *   Either a string or indexed array of strings representing the claim or
   *   nested claim to be set.
   */
  protected function internalUnsetClaim(&$payload, $claim) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (!isset($payload->$current_claim)) {
      return;
    }

    if (is_array($claim) && count($claim) > 0) {
      $this->internalUnsetClaim($payload->$current_claim, $claim);
    }
    else {
      unset($payload->$current_claim);
    }
  }

}
