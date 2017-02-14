<?php

namespace Drupal\jwt\Authentication\Event;

/**
 * Class JwtAuthGenerateEvent.
 *
 * @package Drupal\jwt\Authentication\Event
 */
class JwtAuthGenerateEvent extends JwtAuthBaseEvent {

  /**
   * Adds a claim to a JsonWebToken.
   *
   * @see \Drupal\jwt\JsonWebToken\JsonWebTokenInterface::setClaim()
   */
  public function addClaim($claim, $value) {
    $this->jwt->setClaim($claim, $value);
  }

  /**
   * Removes a claim from a JsonWebToken.
   *
   * @see \Drupal\jwt\JsonWebToken\JsonWebTokenInterface::unsetClaim()
   */
  public function removeClaim($claim) {
    $this->jwt->unsetClaim($claim);
  }

}
