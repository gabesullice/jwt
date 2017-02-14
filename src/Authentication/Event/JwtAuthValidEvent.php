<?php

namespace Drupal\jwt\Authentication\Event;

use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;

/**
 * Class JwtAuthValidEvent.
 *
 * @package Drupal\jwt\Authentication\Provider
 */
class JwtAuthValidEvent extends JwtAuthBaseEvent {
  /**
   * Variable holding the user authenticated by the token in the payload.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function __construct(JsonWebTokenInterface $token) {
    $this->user = User::getAnonymousUser();
    parent::__construct($token);
  }

  /**
   * Sets the authenticated user that will be used for this request.
   *
   * @param \Drupal\user\UserInterface $user
   *   A loaded user object.
   */
  public function setUser(UserInterface $user) {
    $this->user = $user;
  }

  /**
   * Returns a loaded user to use if the token is validated.
   *
   * @return \Drupal\user\UserInterface
   *   A loaded user object
   */
  public function getUser() {
    return $this->user;
  }

}
