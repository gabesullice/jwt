<?php

namespace Drupal\jwt\Authentication\Event;

/**
 * Class JwtAuthEvents.
 *
 * @package Drupal\jwt\Authentication\Event
 */
final class JwtAuthEvents {
  /**
   * Name of the event fired before validating a JWT.
   *
   * This event allows modules to provide custom validations for a JWT.
   * Subscribers should assume every token is invalid. Therefore, this event
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
   * This event fires after a token has been validated. Responders to this event
   * should respond with a valid Drupal user ID. Subscribers may use this event
   * to create new users based on the JWT payload if necessary. Note that this
   * event fires AFTER the token has already been validated. Subscribers should
   * not attempt to prevent authentication during this event. They should
   * instead use the VALIDATE event.
   *
   * @Event
   *
   * @var string
   */
  const VALID = 'jwt.valid';

  /**
   * Name of the event fired before a new JWT is encoded.
   *
   * This event fires prior to a new JWT is encoded. The event contains the
   * payload of the JWT. Subscribers should use this event to add any claims to
   * the JWT before it is given to the client. Bear in mind, JWTs are not
   * encrypted, just signed. Subscribers should not store sensitive information
   * in a JWT.
   *
   * @Event
   *
   * @var string
   */
  const GENERATE = 'jwt.generate';

}
