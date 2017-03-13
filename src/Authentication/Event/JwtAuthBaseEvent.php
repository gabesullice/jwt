<?php

namespace Drupal\jwt\Authentication\Event;

use Symfony\Component\EventDispatcher\Event;
use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;

/**
 * Class JwtAuthBaseEvent.
 *
 * @package Drupal\jwt\Authentication\Event
 */
class JwtAuthBaseEvent extends Event {
  /**
   * The JsonWebToken.
   *
   * @var \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   */
  protected $jwt;

  /**
   * Constructs a JwtAuthEvent with a JsonWebToken.
   *
   * @param \Drupal\jwt\JsonWebToken\JsonWebTokenInterface $token
   *   A decoded JWT.
   */
  public function __construct(JsonWebTokenInterface $token) {
    $this->jwt = $token;
  }

  /**
   * Returns the JWT.
   *
   * @return \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   *   Returns the token.
   */
  public function getToken() {
    return $this->jwt;
  }

}
