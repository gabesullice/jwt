<?php

namespace Drupal\jwt;

use Symfony\Component\HttpFoundation\ParameterBag;

class Jwt implements JwtInterface {

  /**
   * The JWT payload.
   *
   * @var \Symfony\Component\HttpFoundation\ParameterBag
   */
  protected $payload;

  /**
   * A JWT Encoder instance.
   *
   * @var \Drupal\key\JwtEncoderInterface
   */
  protected $jwtEncoder;

  /**
   * Creates a new Jwt instance.
   *
   * @param array $payload
   *   The payload of the JWT to be created.
   */
  public function __construct($payload) {
    $this->payload = new ParameterBag($payload);
  }

  /**
   * {@inheritdoc}
   */
  public function encode() {
    return $this->getJwtEncoder()->encode($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * {@inheritdoc}
   */
  public function setJwtEncoder(JwtEncoderInterface $jwt_encoder) {
    $this->jwtEncoder = $jwt_encoder;
  }

  /**
   * Returns an instance of a config factory.
   *
   * @return \Drupal\Core\Config\ConfigFactory
   *   A config factory instance.
   */
  protected function getJwtEncoder() {
    if (is_null($this->jwtEncoder)) {
      $this->setJwtEncoder(\Drupal::service('jwt.transcoder'));
    }
    return $this->jwtEncoder;
  }

}
