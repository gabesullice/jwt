<?php

/**
 * @file
 * Contains Drupal\jwt\Form\ConfigForm.
 */

namespace Drupal\jwt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\jwt\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'jwt.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jwt_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['jwt_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('JWT Secret'),
      '#default_value' => $this->config('jwt.config')->get('key_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    if (isset($values['jwt_key'])) {
      $this->config('jwt.config')->set('key_id', $values['jwt_key'])->save();
    }
  }

}
