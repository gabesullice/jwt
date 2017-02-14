<?php

namespace Drupal\jwt\Authentication\Event;

/**
 * Class JwtAuthValidateEvent.
 *
 * @package Drupal\jwt\Authentication\Event
 */
class JwtAuthValidateEvent extends JwtAuthBaseEvent {

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
   * Marks a token as invalid and stops further propagation of the event.
   *
   * This marks a given token as invalid. You should provide a reason for
   * invalidating the token. This message will not be kept private, so one
   * should be cautious of leaking secure information here.
   *
   * @param string $reason
   *   The reason that this token was invalidated.
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
   *   Returns if the token is valid.
   */
  public function isValid() {
    return $this->valid;
  }

  /**
   * Returns a string describing why a JWT was considered invalid.
   *
   * @return string
   *   The reason the token is invalid.
   */
  public function invalidReason() {
    return $this->invalidReason;
  }

}
