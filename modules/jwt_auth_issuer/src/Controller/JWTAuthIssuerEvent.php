<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvent.
 */

namespace Drupal\jwt_auth_issuer\Controller;

use Drupal\jwt\Authentication\Provider\JWTAuthEvent;

class JWTAuthIssuerEvent extends JWTAuthEvent {

  /**
   * Add a claim to the JWT payload.
   *
   * @param mixed $claim
   *  Either a string or indexed array of strings representing the claim or
   *  nested claim to be set.
   */
  public function addClaim($claim, $value) {
    $payload = $this->payload;
    $this->setClaim($payload, $claim, $value);
    $this->payload = $payload;
  }

  /**
   * Remove a claim from the JWT payload.
   *
   * @param mixed $claim
   *  Either a string or indexed array of strings representing the claim or
   *  nested claim to be set.
   */
  public function removeClaim($claim) {
    $payload = $this->payload;
    $this->unsetClaim($payload, $claim, $value);
    $this->payload = $payload;
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
  private function setClaim(&$payload, $claim, $value) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (is_array($claim) && count($claim) > 0) {
      if (!isset($payload->$current_claim)) {
        $payload->$current_claim = new \stdclass();
      }

      $this->setClaim($payload->$current_claim, $claim, $value);
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
   * @param mixed $value
   *  The value to set for the given claim.
   */
  private function unsetClaim(&$payload, $claim) {
    $current_claim = (is_array($claim)) ? array_shift($claim) : $claim;

    if (is_array($claim) && count($claim) > 0) {
      if (!isset($payload->$current_claim)) {
        return;
      }

      $this->unsetClaim($payload->$current_claim, $claim);
    }
    else {
      unset($payload->$current_claim);
    }
  }

}
