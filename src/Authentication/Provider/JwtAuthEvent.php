<?php

/**
 * @file
 * Contains \Drupal\jwt\Authentication\Provider\JwtAuthEvent.
 */

namespace Drupal\jwt\Authentication\Provider;

use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\Event;

class JwtAuthEvent extends Event {
  /**
   * Variable holding the user authenticated by the token in the payload.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The JsonWebToken.
   *
   * @var \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   */
  protected $jwt;

  /**
   * Variable tracking whether a token has been marked invalid.
   *
   * @var bool
   */
  protected $valid = TRUE;

  /**
   * Variable holding a reason that a token was marked invalid.
   *
   * @var string
   */
  protected $invalidReason;

  /**
   * Constructs a JwtAuthEvent with a JsonWebToken.
   *
   * @param $token
   *  A decoded JWT.
   */
  public function __construct($token) {
    $this->jwt = $token;
    $this->user = User::getAnonymousUser();
  }

  /**
   * Sets the authenticated user that will be used for this request.
   *
   * @param \Drupal\user\UserInterface $user
   *  A loaded user object.
   */ public function setUser(UserInterface $user) {
    $this->user = $user;
  }

  /**
   * Returns a loaded user to use if the token is validated.
   *
   * @return \Drupal\user\UserInterface $user
   *  A loaded user object
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Returns the JWT.
   *
   * @return \Drupal\jwt\JsonWebToken\JsonWebTokenInterface
   */
  public function getToken() {
    return $this->jwt;
  }

  /**
   * Marks a token as invalid and stops further propogation of the event.
   *
   * This marks a given token as invalid. You should provide a reason for
   * invalidating the token. This message will not be kept private, so one
   * should be cautious of leaking secure information here.
   *
   * @param string $reason
   *  The reason that this token was invalidated.
   */
  public function invalidate($reason) {
    $this->valid = FALSE;
    $this->invalidReason = $reason;
    $this->stopPropagation();
  }

  /**
   * Returns whether a token was considered valid.
   *
   * @return bool
   */
  public function isValid() {
    return $this->valid;
  }

  /**
   * Returns a string describing why a JWT was considered invalid.
   *
   * @return bool
   */
  public function invalidReason() {
    return $this->invalidReason;
  }

}
