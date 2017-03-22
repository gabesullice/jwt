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
 *     "uid" = "uid",
 *   }
 * )
 */
class JwtRefreshToken extends ContentEntityBase implements JwtRefreshTokenInterface {

  /**
   * @inheritDoc
   */
  public function isExpired() {
    return $this->get('expires')->getString() < REQUEST_TIME;
  }

  /**
   * Default TTL.
   *
   * One week.
   */
  const TTL = 60 * 60 * 24 * 7;

  /**
   * @inheritDoc
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['jti'] = BaseFieldDefinition::create('string')
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
    $fields['expires'] = BaseFieldDefinition::create('timestamp')
      ->setCardinality(1)
      ->setLabel(t('Expires'))
      ->setDefaultValueCallback('Drupal\jwt_auth_refresh\Entity\JwtRefreshToken::expires')
      ->setDescription(t('The time the token expires.'));
    return $fields;
  }

  /**
   * Generate a default value for the token.
   *
   * @return string[]
   */
  public static function tokenGenerate() {
    $default = [(new Random())->string(8, TRUE)];
    return $default;
  }

  /**
   * Generate default value for the expires time.
   *
   * @return string[]
   */
  public static function expires() {
    return [REQUEST_TIME + self::TTL];
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
