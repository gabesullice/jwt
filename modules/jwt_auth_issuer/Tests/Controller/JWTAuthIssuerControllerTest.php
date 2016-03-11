<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Tests\JWTAuthIssuerController.
 */

namespace Drupal\jwt_auth_issuer\Tests;

use Drupal\simpletest\WebTestBase;
use Firebase\JWT\JWT;

/**
 * Provides automated tests for the jwt_auth_issuer module.
 */
class JWTAuthIssuerControllerTest extends WebTestBase {

  /**
   * Firebase\JWT\JWT definition.
   *
   * @var Firebase\JWT\JWT
   */
  protected $jwt_firebase_php-jwt;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "jwt_auth_issuer JWTAuthIssuerController's controller functionality",
      'description' => 'Test Unit for module jwt_auth_issuer and controller JWTAuthIssuerController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests jwt_auth_issuer functionality.
   */
  public function testJWTAuthIssuerController() {
    // Check that the basic functions of module jwt_auth_issuer.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
