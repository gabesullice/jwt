<?php

namespace Drupal\jwt\Plugin\KeyType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\key\Plugin\KeyTypeBase;
use Drupal\key\Plugin\KeyPluginFormInterface;

/**
 * Defines a key type for JWT HMAC Signatures.
 *
 * @KeyType(
 *   id = "jwt_hs",
 *   label = @Translation("JWT HMAC Key"),
 *   description = @Translation("A key used for JWT HMAC algorithms."),
 *   group = "encryption",
 *   key_value = {
 *     "plugin" = "text_field"
 *   }
 * )
 */
class JwtHsKeyType extends KeyTypeBase implements KeyPluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'algorithm' => 'HS256',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $algorithm_options = [
      'HS256' => $this->t('HMAC using SHA-256 (HS256)'),
      'HS384' => $this->t('HMAC using SHA-384 (HS384)'),
      'HS512' => $this->t('HMAC using SHA-512 (HS512)'),
    ];

    $algorithm = $this->getConfiguration()['algorithm'];

    $form['algorithm'] = array(
      '#type' => 'select',
      '#title' => $this->t('JWT Algorithm'),
      '#description' => $this->t('The JWT Algorithm to use with this key.'),
      '#options' => $algorithm_options,
      '#default_value' => $algorithm,
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration($form_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public static function generateKeyValue(array $configuration) {
    $algorithm_keysize = self::getAlgorithmKeysize();

    $algorithm = $configuration['algorithm'];

    if (!empty($algorithm) && isset($algorithm_keysize[$algorithm])) {
      $bytes = $algorithm_keysize[$algorithm] / 8;
    }
    else {
      $bytes = $algorithm_keysize['HS256'] / 8;
    }
    $random_key = Crypt::randomBytes($bytes);

    return $random_key;
  }

  /**
   * {@inheritdoc}
   */
  public function validateKeyValue(array $form, FormStateInterface $form_state, $key_value) {
    if (!$form_state->getValue('algorithm')) {
      return;
    }

    // Validate the key size.
    $algorithm = $form_state->getValue('algorithm');
    $bytes = self::getAlgorithmKeysize()[$algorithm] / 8;
    if (strlen($key_value) < $bytes) {
      $form_state->setErrorByName('algorithm', $this->t('Key size (%size bits) is too small for algorithm chosen. Algorithm requires a minimum of %required bits.', ['%size' => strlen($key_value) * 8, '%required' => $bytes * 8]));
    }
  }

  /**
   * Get keysizes for the various algorithms.
   *
   * @return array
   *   An array key keysizes.
   */
  protected static function getAlgorithmKeysize() {
    return [
      'HS256' => 512,
      'HS384' => 1024,
      'HS512' => 1024,
    ];
  }

}
