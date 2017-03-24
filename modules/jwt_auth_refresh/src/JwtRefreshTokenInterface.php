<?php

namespace Drupal\jwt_auth_refresh;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

interface JwtRefreshTokenInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Determine if the token is expired.
   *
   * @return bool
   */
  public function isExpired();

}
