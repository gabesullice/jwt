<?php

/**
 * @file
 * Contains \Drupal\jwt\Authentication\Provider\JwtAuthEvents.
 */

namespace Drupal\jwt\Authentication\Provider;

final class JwtAuthEvents {

  /**
   * Name of the event fired before validating a JWT.
   *
   * This event allows modules to provide custom validations for a JWT.
   * Subscibers should assume every token is invalid. Therefore, this event
   * should NOT perform any actions that depend on a valid JWT. This allows
   * other subscribers to invalidate the JWT. Actions that depend on a valid
   * token should use the VALID event.
   *
   * @Event
   *
   * @var string
   */
  const VALIDATE = 'jwt.validate';

  /**
   * Name of the event fired after a JWT has been validated.
   *
   * This event fires after a token has been validated. Resonders to this event
   * should respond with a valid Drupal user ID. Subscibers may use this event
   * to create new users based on the JWT payload if necessary. Note that this
   * event fires AFTER the token has already been validated. Subscibers should
   * not attempt to prevent authentication during this event. They should
   * instead use the VALIDATE event.
   *
   * @Event
   *
   * @var string
   */
  const VALID = 'jwt.valid';

}
