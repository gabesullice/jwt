<?php

namespace Drupal\jwt\Validator;

/**
 * Class JwtInvalidException.
 *
 * @package Drupal\jwt\Validator
 */
class JwtInvalidException extends \Exception {

  const DECODE_ERROR = 1;

}
