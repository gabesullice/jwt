<?php

namespace Drupal\jwt_auth_refresh;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\jwt\JsonWebToken\JsonWebToken;
use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt_auth_refresh\Entity\JwtRefreshToken;

class JwtRefreshTokens implements JwtRefreshTokensInterface {

  /**
   * Entity Type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Transcoder.
   *
   * @var \Drupal\jwt\Transcoder\JwtTranscoderInterface
   */
  protected $transcoder;

  /**
   * @inheritDoc
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, JwtTranscoderInterface $jwtTranscoder) {
    $this->entityTypeManager = $entityTypeManager;
    $this->transcoder = $jwtTranscoder;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveForUser(AccountInterface $account) {
    $token = JwtRefreshToken::create([
      'uid' => $account->id(),
    ]);
    $token->save();
    $jwt = new JsonWebToken((object) [
      'jti' => $token->get('jti')->getString(),
      'exp' => $token->get('expires')->getString(),
    ]);
    return $this->transcoder->encode($jwt);
  }

}
