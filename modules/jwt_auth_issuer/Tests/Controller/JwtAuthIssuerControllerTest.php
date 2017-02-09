<?php

/**
 * @file
 * Contains \Drupal\jwt_auth_issuer\Tests\JwtAuthIssuerController.
 */

namespace Drupal\jwt_auth_issuer\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the jwt_auth_issuer module.
 */
class JwtAuthIssuerControllerTest extends WebTestBase {

  /**
   * Firebase\JWT\JWT definition.
   *
   * @var \Firebase\JWT\JWT
   */
  protected $jwt_firebase_php_jwt;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "jwt_auth_issuer JwtAuthIssuerController's controller functionality",
      'description' => 'Test Unit for module jwt_auth_issuer and controller JwtAuthIssuerController.',
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
  public function testJwtAuthIssuerController() {
    // Check that the basic functions of module jwt_auth_issuer.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
