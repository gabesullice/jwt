<?php

namespace Drupal\jwt_auth_refresh;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jwt_auth_refresh\Entity\JwtRefreshToken;

class JwtRefreshTokens implements JwtRefreshTokensInterface {

  /**
   * Entity Type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @inheritDoc
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveForUser(AccountInterface $account) {
    $existing = $this->entityTypeManager->getStorage('jwt_refresh_token')->getQuery()
      ->condition('uid.target_id', $account->id())
      ->condition('status', 1)
      ->execute();
    if ($existing) {
      $token = JwtRefreshToken::load(reset($existing));
    }
    else {
      $token = JwtRefreshToken::create(['uid' => $account->id()]);
      $token->save();
    }
    return $token;
  }

}
