<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Controller\JWTAuthIssuerEvents.
 */

namespace Drupal\jwt_auth_issuer\Controller;

final class JWTAuthIssuerEvents {

  /**
   * Name of the event fired before a new JWT is encoded.
   *
   * This event fires prior to a new JWT is encoded. The event contains the
   * payload of the JWT. Subscibers should use this event to add any claims to
   * the JWT before it is given to the client. Bear in mind, JWTs are not
   * encrypted, just signed. Subscibers should not store sensitive information
   * in a JWT.
   *
   * @Event
   *
   * @var string
   */
  const GENERATE = 'jwt_auth_issuer.generate';

}
