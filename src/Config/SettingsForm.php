<?php

namespace Drupal\jwt\Config;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Firebase\JWT\JWT as FirebaseJWT;

class SettingsForm extends ConfigFormBase {

  /**
   * The JWT config name.
   */
  const CONFIG_NAME = 'jwt.settings';

  /**
   * The JWT settings form id.
   */
  const SETTINGS_FORM_ID = 'jwt_settings_form';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::SETTINGS_FORM_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(
      'form.jwt-settings-form',
      $form
    ));
    return $response;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfig();

    $form['supported_algorithms'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Supported Algorithms'),
      '#description' => $this->t('Please check all algorithms that you would like to support for encoding and/or decoding.'),
      '#options' => $this->algorithmOptions(),
      '#default_value' => $config->get('supported_algorithms'),
      '#ajax' => ['callback' => [$this, 'ajaxCallback']],
      '#required' => TRUE,
    ];

    $supported_algorithms = array_filter($form_state->getValue(
      'supported_algorithms',
      $config->get('supported_algorithms', [])
    ));

    $this->formSigningAlgorithms($form, $form_state);
    $this->formKeyConfiguration($form, $form_state);

    return parent::buildForm($form, $form_state);
  }

  protected function formKeyConfiguration(array &$form, FormStateInterface $form_state) {
    $config = $this->getConfig();

    $supported_algorithms = array_filter($form_state->getValue(
      'supported_algorithms',
      $config->get('supported_algorithms', [])
    ));

    if (array_key_exists('key_config', $form)) {
      $form['key_config'] = array_intersect_key($form['key_config'], $supported_algorithms);
    }
    else {
      $form['key_config'] = [];
    }

    $form['key_config'] = array_reduce($supported_algorithms, function ($key_config, $algorithm) use ($config) {
      $key_config[$algorithm] = [
        '#type' => 'fieldset',
        '#title' => $algorithm,
      ];

      $config_form_key = $this->getConfigFormKey($algorithm, 'verify');
      $key_config[$algorithm][$config_form_key] = [
        '#type' => 'key_select',
        '#title' => $this->t('@alg Verification Key', ['@alg' => $algorithm]),
        '#description' => $this->t('Please select the key to use verifying JWTs using the @alg algorithm.', ['@alg' => $algorithm]),
        '#default_value' => $config->get($config_form_key),
        '#key_filters' => [
          'type_group' => 'encryption',
        ],
      ];

      return $key_config;
    }, $form['key_config']);

    $signing_algorithms = array_filter($form_state->getValue(
      'signing_algorithms',
      $config->get('signing_algorithms', [])
    ));

    $form['key_config'] = array_reduce($supported_algorithms, function ($key_config, $algorithm) use ($signing_algorithms, $config) {
      $config_form_key = $this->getConfigFormKey($algorithm, 'sign');
      if (!in_array($algorithm, $signing_algorithms)) {
        unset($key_config[$algorithm][$config_form_key]);
      }
      else {
        $key_config[$algorithm][$config_form_key] = [
          '#type' => 'key_select',
          '#title' => $this->t('@alg Signing Key', ['@alg' => $algorithm]),
          '#description' => $this->t('Please select the key to use signing JWTs using the @alg algorithm.', ['@alg' => $algorithm]),
          '#default_value' => $config->get($config_form_key),
          '#key_filters' => [
            'type_group' => 'encryption',
          ],
        ];
      }
      return $key_config;
    }, $form['key_config']);
  }

  protected function formSigningAlgorithms(array &$form, FormStateInterface $form_state) {
    $config = $this->getConfig();

    $supported_algorithms = array_filter($form_state->getValue(
      'supported_algorithms',
      $config->get('supported_algorithms', [])
    ));

    if (empty($supported_algorithms)) {
      unset($form['signing_algorithms']);
      unset($form['default_algorithm']);
    }
    else {
      $form['signing_algorithms'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Supported Signing Algorithms'),
        '#description' => $this->t('Please check all algorithms that you would like to support for encoding.'),
        '#options' => array_combine($supported_algorithms, $supported_algorithms),
        '#default_value' => $config->get('signing_algorithms'),
        '#ajax' => ['callback' => [$this, 'ajaxCallback']],
      ];

      $signing_algorithms = array_filter($form_state->getValue(
        'signing_algorithms',
        $config->get('signing_algorithms', [])
      ));

      if (!empty($signing_algorithms)) {
        $form['default_algorithm'] = [
          '#type' => 'select',
          '#title' => $this->t('Signing Algorithm'),
          '#description' => $this->t('Please select the default signing algorithm.'),
          '#options' => array_combine($signing_algorithms, $signing_algorithms),
          '#default_value' => $config->get('default_algorithm'),
          '#ajax' => ['callback' => [$this, 'ajaxCallback']],
        ];
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->getConfig();
    $config->delete();

    $supported_algorithms = array_values(array_filter($form_state->getValue('supported_algorithms', [])));
    $signing_algorithms = array_values(array_filter($form_state->getValue('signing_algorithms', [])));
    $default_algorithm = $form_state->getValue('default_algorithm');
    $config->set('supported_algorithms', $supported_algorithms);
    $config->set('signing_algorithms', $signing_algorithms);
    $config->set('default_algorithm', $default_algorithm);

    foreach ($supported_algorithms as $algorithm) {
      $config_form_key = $this->getConfigFormKey($algorithm, 'verify');
      $config->set($config_form_key, $form_state->getValue($config_form_key));
      if (in_array($algorithm, $signing_algorithms)) {
        $config_form_key = $this->getConfigFormKey($algorithm, 'sign');
        $config->set($config_form_key, $form_state->getValue($config_form_key));
      }
    }

    $config->save();
  }

  protected static function getConfigFormKey($algorithm, $type) {
    return sprintf('keys_%s_%s', $algorithm, $type);
  }

  protected function getConfig() {
    return $this->config(self::CONFIG_NAME);
  }

  protected static function algorithmOptions() {
    $algs = array_keys(FirebaseJWT::$supported_algs);
    return array_combine($algs, $algs);
  }

}
