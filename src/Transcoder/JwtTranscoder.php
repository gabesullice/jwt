<?php

/**
 * @file
 * Contains \Drupal\jwt\Transcoder\JwtTranscoder.
 */

namespace Drupal\jwt\Transcoder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\jwt\JsonWebToken\JsonWebToken;
use Drupal\jwt\JsonWebToken\JsonWebTokenInterface;
use Drupal\key\KeyRepositoryInterface;
use Firebase\JWT\JWT;

/**
 * Class JwtTranscoder.
 *
 * @package Drupal\jwt
 */
class JwtTranscoder implements JwtTranscoderInterface {

  /**
   * The firebase/php-jwt transcoder.
   *
   * @var \Firebase\JWT\JWT
   */
  protected $transcoder;

  /**
   * The allowed algorithms with which a JWT can be decoded.
   */
  protected $algorithms = array('HS256');

  /**
   * The key used to encode/decode a JsonWebToken.
   */
  protected $secret;

  /**
   * Contructs a new JwtTranscoder.
   */
  public function __construct(JWT $php_jwt, ConfigFactoryInterface $configFactory, KeyRepositoryInterface $key_repo) {
    $this->transcoder = $php_jwt;

    $key_id = $configFactory->get('jwt.config')->get('key_id');
    if (isset($key_id)) {
      $key = $key_repo->getKey($key_id);

      if (!is_null($key)) {
        $secret = $key->getKeyValue();
        $this->setSecret($secret);
      }
    }
  }

  public function setSecret($secret) {
    $this->secret = $secret;
  }

  /**
   * @inheritdoc
   */
  public function decode($jwt) {
    try {
      $token = $this->transcoder->decode($jwt, $this->secret, $this->algorithms);
    } catch (\Exception $e) {
      throw JwtDecodeException::newFromException($e);
    }
    return new JsonWebToken($token);
  }

  /**
   * Encodes a JsonWebToken.
   */
  public function encode(JsonWebTokenInterface $jwt, $options = []) {
    $options = $this->getOptions($options);
    $encoded = $this->transcoder->encode($jwt->getPayload(), $options['key'], $options['alg']);
    return $encoded;
  }

  /**
   * Gets a standard set of options for encoding a JWT, with overrides.
   */
  protected function getOptions($options = array()) {
    $defaults = array(
      'alg' => 'HS256',
      'key' => $this->secret,
    );

    return array_merge_recursive($options, $defaults);
  }

}
