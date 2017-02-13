<?php

namespace Drupal\jwt\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cache policy for pages served from JWT auth.
 *
 * This policy disallows caching of requests that use jwt_auth for security
 * reasons. Otherwise responses for authenticated requests can get into the
 * page cache and could be delivered to unprivileged users.
 */
class DisallowJwtAuthRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    $auth = $request->headers->get('Authorization');
    if (preg_match('/^Bearer .+/', $auth)) {
      return self::DENY;
    }

    return NULL;
  }

}
