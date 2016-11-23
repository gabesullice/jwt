<?php

namespace Drupal\jwt;

use Drupal\Core\Config\ConfigFactory;

use Drupal\key\KeyRepositoryInterface;

use \Firebase\JWT\JWT as FirebaseJWT;

class JwtTranscoder implements JwtTranscoderInterface {

  /**
   * The algorithm to use.
   *
   * @var string
   */
  protected $algorithm;

  /**
   * A config factory instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A key repository instance.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  public function __construct(ConfigFactory $config_factory, KeyRepositoryInterface $key_repository) {
    $this->setConfigFactory($config_factory);
    $this->setKeyRepository($key_repository);
  }

  /**
   * {@inheritdoc}
   */
  public function encode(JwtInterface $jwt) {
    $payload = $jwt->getPayload()->all();
    $encoded = FirebaseJWT::encode(
      $payload,
      $this->getKey($this->getAlgorithm(), 'sign'),
      $this->getAlgorithm()
    );
    return $encoded;
  }

  /**
   * {@inheritdoc}
   */
  public function decode($encoded) {
    $supported_algorithms = $this->supportedAlgorithms();
    try {
      $decoded = (array) FirebaseJWT::decode(
        $encoded,
        $this->getKeys($supported_algorithms),
        $supported_algorithms
      );
    } catch (\Exception $e) {
      throw JwtEncoderException::fromException($e);
    }
    return $this->newJwt($decoded);
  }

  /**
   * {@inheritdoc}
   */
  public function newJwt($payload) {
    $jwt = new Jwt($payload);
    $jwt->setJwtEncoder($this);
    return $jwt;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlgorithm() {
    if (is_null($this->algorithm)) {
      $this->setAlgorithm($this->defaultAlgorithm());
    }
    if (is_null($this->algorithm)) {
      throw new JwtEncoderException('No algorithm available.');
    }
    return $this->algorithm;
  }

  /**
   * {@inheritdoc}
   */
  public function setAlgorithm($algorithm = FALSE) {
    $alg = ($algorithm === FALSE) ? $this->defaultAlgorithm() : $algorithm;

    if (is_null($alg) || in_array($alg, $this->supportedAlgorithms())) {
      $this->algorithm = $alg;
    }
    else {
      throw new JwtEncoderException('Algorithm not supported.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function supportedAlgorithms() {
    return $this->getConfig()->get('supported_algorithms');
  }

  /**
   * {@inheritdoc}
   */
  public function signingAlgorithms() {
    return $this->getConfig()->get('signing_algorithms');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultAlgorithm() {
    return $this->getConfig()->get('default_algorithm');
  }

  /**
   * Returns a map of algorithms and keys.
   *
   * @param array $algorithms
   *   A list of algorithms for which to fetch keys.
   *
   * @return array
   *   An associative array, keyed by algorithm with the appropriate key as a
   *   value.
   */
  protected function getKeys(array $algorithms) {
    return array_reduce($algorithms, function ($keys, $algorithm) {
      $keys[$algorithm] = $this->getKey($algorithm);
      return $keys;
    }, []);
  }

  /**
   * Returns the configured secret key for the given algorithm and operation.
   *
   * @param string $algorithm
   *   The algorithm for which to fetch a key.
   * @param string $operation
   *   (optional) The operation for which the key will be used.
   *   Acceptable values: 'sign'|'verify'
   *   Default: 'verify'
   *
   * @return string
   *   The secret key.
   */
  protected function getKey($algorithm, $operation = 'verify') {
    $config_key = sprintf('keys_%s_%s', $algorithm, $operation);
    $key_id = $this->getConfig()->get($config_key);
    $key = $this->getKeyRepository()->getKey($key_id);
    return $key->getKeyValue();
  }

  /**
   * Returns the JWT config entity.
   */
  protected function getConfig() {
    return $this->getConfigFactory()->get(Config\SettingsForm::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function setKeyRepository(KeyRepositoryInterface $key_repository) {
    $this->keyRepository = $key_repository;
  }

  /**
   * Returns an instance of a key repository.
   *
   * @return \Drupal\key\KeyRepositoryInterface
   *   A key repository interface.
   */
  protected function getKeyRepository() {
    if (is_null($this->keyRepository)) {
      $this->setKeyRepository(\Drupal::service('key.repository'));
    }
    return $this->keyRepository;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigFactory(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Returns an instance of a config factory.
   *
   * @return \Drupal\Core\Config\ConfigFactory
   *   A config factory instance.
   */
  protected function getConfigFactory() {
    if (is_null($this->configFactory)) {
      $this->setConfigFactory(\Drupal::service('config.factory'));
    }
    return $this->configFactory;
  }

}
