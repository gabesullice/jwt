<?php

/**
 * @file
 * Contains \Drupal\jwt\Trancoder\JwtDecodeException.
 */

namespace Drupal\jwt\Transcoder;

use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

/**
 * Class JwtDecodeException.
 *
 * @package Drupal\jwt\Trancoder
 */
class JwtDecodeException extends \Exception {

  const DOMAIN            = 1;
  const UNEXPECTED_VALUE  = 2;
  const SIGNATURE_INVALID = 3;
  const BEFORE_VALID      = 4;
  const EXPIRED           = 5;
  const UNKNOWN           = 6;

  /**
   * Contruct a new decode exception from a php-jwt exception.
   */
  public static function newFromException(\Exception $e) {
    switch ($e) {
    case ($e instanceof SignatureInvalidException):
      return new static($e->getMessage(), self::SIGNATURE_INVALID, $e);
    case ($e instanceof BeforeValidException):
      return new static($e->getMessage(), self::BEFORE_VALID, $e);
    case ($e instanceof ExpiredException):
      return new static($e->getMessage(), self::EXPIRED, $e);
    case ($e instanceof \Exception):
      return new static('Internal Server Error', self::UNKNOWN, $e);
    default:
      return new static('Internal Server Error', self::UNKNOWN, $e);
    }
  }

}
