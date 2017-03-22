<?php

namespace Drupal\jwt_auth_refresh\Entity;

use Drupal\Component\Utility\Random;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\jwt_auth_refresh\JwtRefreshTokenInterface;
use Drupal\user\UserInterface;
use Firebase\JWT\JWT;

/**
 * @ContentEntityType(
 *   id = "jwt_refresh_token",
 *   label = @Translation("JWT Refresh Token"),
 *   base_table = "jwt_refresh_token",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "uid" = "uid",
 *   }
 * )
 */
class JwtRefreshToken extends ContentEntityBase implements JwtRefreshTokenInterface {

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->get('token')->getString();
  }

  /**
   * @inheritDoc
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Refresh Token'))
      ->setDescription(t('The refresh token'))
      ->setRequired(TRUE)
      ->setDefaultValueCallback('Drupal\jwt_auth_refresh\Entity\JwtRefreshToken::tokenGenerate')
      ->setCardinality(1);
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The associated user.'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setDisplayConfigurable('form', TRUE);
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDescription(t('Whether the token is active.'))
      ->setDefaultValue(TRUE);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDefaultValueCallback('time')
      ->setDescription(t('The time that the token was created.'));
    return $fields;
  }

  /**
   * Generate a default value for the token.
   *
   * @return string[]
   */
  public static function tokenGenerate() {
    $default = [JWT::urlsafeB64Encode((new Random())->string(64, TRUE))];
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

}
