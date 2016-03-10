<?php

/**
 * @file
 * Contains \Drupal\jwt\Authentication\Provider\JWTAuth.
 */

namespace Drupal\jwt\Authentication\Provider;

use Drupal\jwt\Authentication\Provider\JWTAuthEvent;
use Drupal\jwt\Authentication\Provider\JWTAuthEvents;
use Drupal\user\UserAuthInterface;
use Drupal\key\KeyRepositoryInterface;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Flood\FloodInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Firebase\JWT\JWT;
use Firebase\JWT\DomainException;
use Firebase\JWT\UnexpectedValueException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

/**
 * JWT Authentication Provider.
 */
class JWTAuth implements AuthenticationProviderInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The user auth service.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * A decoded JWT payload.
   *
   * @var object
   */
  protected $token;

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The JWT service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $phpJWT;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The key repository service.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepo;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication service.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Firebase\JWT\JWT $php_jwt
   *   The jwt validator service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher interface.
   * @param \Drupal\key\KeyRepositoryInterface $keyRepo
   *   The event dispatcher interface.
   */
  public function __construct(ConfigFactoryInterface $config_factory, UserAuthInterface $user_auth, FloodInterface $flood, JWT $php_jwt, EventDispatcherInterface $event_dispatcher, KeyRepositoryInterface $key_repo) {
    $this->configFactory = $config_factory;
    $this->userAuth = $user_auth;
    $this->flood = $flood;
    $this->phpJWT = $php_jwt;
    $this->eventDispatcher = $event_dispatcher;
    $this->keyRepo = $key_repo;
  }

  /**
   * {@inheritdoc}
   */
	public function applies(Request $request) {
    $auth = $request->headers->get('Authorization');
		return preg_match('/^Bearer .+/', $auth);
	}

  /**
   * {@inheritdoc}
   */
	public function authenticate(Request $request) {
    if (!$token_string = $this->getToken($request)) {
      throw new AccessDeniedHttpException('No token provided');
      return null;
    }

    $secret = $this->keyRepo->getKey('jwt_key')->getKeyValue();

    try {
      $this->token = $this->validate($token_string, $secret);
    } catch (AccessDeniedHttpException $e) {
      throw $e;
      return null;
    } 

    if (!$user = $this->getUser()) {
      throw new AccessDeniedHttpException('Unable to load user from token');
      return null;
    }

    return $user;
	}

  /**
   * Allow the system to interpret token and provide a user id.
   *
   * @return mixed $uid
   *  A loaded user object.
   */
  private function getUser() {
    $event = new JWTAuthEvent($this->token);
    $this->eventDispatcher->dispatch(JWTAuthEvents::VALID, $event);
    return $event->getUser();
  }

  /**
   * Gets the JWT token from the request header, if it exists.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *  The http request object.
   */
  private function getToken(Request $request) {
    $auth_header = $request->headers->get('Authorization');
    $matches = array();
    if (!$hasJWT = preg_match('/^Bearer (.*)/', $auth_header, $matches)) {
      return FALSE;
    }
    return $matches[1];
  }

  /**
   * {@inheritdoc}
   */
  public function handleException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();
    if ($exception instanceof AccessDeniedHttpException) {
      $event->setException(new UnauthorizedHttpException('Invalid JWT.', $exception));
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function challengeException(Request $request, \Exception $previous) {
    $site_name = $this->configFactory->get('system.site')->get('name');
    $challenge = SafeMarkup::format('Basic realm="@realm"', array(
      '@realm' => !empty($site_name) ? $site_name : 'Access restricted',
    ));
    return new UnauthorizedHttpException((string) $challenge, 'No authentication credentials provided.', $previous);
  }

  /**
   * Validates a JWT.
   */
  private function validate($jwt, $secret) {
    $key = $this->phpJWT->urlsafeB64Decode($secret);
    try {
      $token = $this->phpJWT->decode($jwt, $secret, array('HS256'));
    } catch (DomainException $e) {
      # Algorithm was not provided
      throw new AccessDeniedHttpException('Internal Server Error', $e);
      return null;
    } catch (UnexpectedValueException $e) {
			# Provided JWT was invalid
			throw new AccessDeniedHttpException($e->getMessage(), $e);
			return null;
		} catch (SignatureInvalidException $e) {
			# Provided JWT was invalid because the signature verification failed
			throw new AccessDeniedHttpException($e->getMessage(), $e);
			return null;
		} catch (BeforeValidException $e) {
			# Provided JWT is trying to be used before it's eligible as defined by 'nbf'
			throw new AccessDeniedHttpException($e->getMessage(), $e);
			return null;
		} catch (ExpiredException $e) {
			# Provided JWT has since expired, as defined by the 'exp' claim
			throw new AccessDeniedHttpException($e->getMessage(), $e);
			return null;
		} catch (Exception $e) {
      # Some unknown exception
      throw new AccessDeniedHttpException('Internal Server Error', $e);
      return null;
    }

    $event = new JWTAuthEvent($token);
    $this->eventDispatcher->dispatch(JWTAuthEvents::VALIDATE, $event);

    if ($event->isValid()) {
      return $token;
    } else {
      throw new AccessDeniedHttpException($event->invalidReason());
      return null;
    }
  }
}
