<?php

namespace Drupal\jwt;

use UnexpectedValueException;
use \Firebase\JWT\DomainException;
use \Firebase\JWT\SignatureInvalidException;
use \Firebase\JWT\BeforeValidException;
use \Firebase\JWT\ExpiredException;

class JwtDecoderException extends \Exception {

  const DOMAIN            = 1;
  const UNEXPECTED_VALUE  = 2;
  const SIGNATURE_INVALID = 3;
  const BEFORE_VALID      = 4;
  const EXPIRED           = 5;
  const UNKNOWN           = 6;

  /**
   * Contruct a new decode exception from a php-jwt exception.
   */
  public static function fromException(\Exception $e) {
    switch ($e) {
    case ($e instanceof DomainException):
      return new static($e->getMessage(), self::DOMAIN, $e);
    case ($e instanceof UnexpectedValueException):
      return new static($e->getMessage(), self::UNEXPECTED_VALUE, $e);
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
