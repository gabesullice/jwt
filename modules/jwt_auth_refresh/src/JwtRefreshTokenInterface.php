<?php

namespace Drupal\jwt_auth_refresh;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

interface JwtRefreshTokenInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Getter for the token property.
   *
   * @return string The refresh token.
   */
  public function getToken();

}
