<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aura\Session\Segment;

use Aviat\Banker\Exception\InvalidArgumentException;
use const Aviat\AnimeClient\SESSION_SEGMENT;

use Aviat\AnimeClient\API\{
	CacheTrait,
	Kitsu as K
};
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Event;

use Throwable;
use const PHP_SAPI;

/**
 * Kitsu API Authentication
 */
final class Auth {
	use CacheTrait;
	use ContainerAware;

	/**
	 * Anime API Model
	 *
	 * @var Model
	 */
	private Model $model;

	/**
	 * Session object
	 *
	 * @var Segment
	 */
	private Segment $segment;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->setCache($container->get('cache'));
		$this->segment = $container->get('session')
			->getSegment(SESSION_SEGMENT);
		$this->model = $container->get('kitsu-model');

		Event::on('::unauthorized::', [$this, 'reAuthenticate']);
	}

	/**
	 * Make the appropriate authentication call,
	 * and save the resulting auth token if successful
	 *
	 * @param string $password
	 * @return boolean
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function authenticate(string $password): bool
	{
		$config = $this->container->get('config');
		$username = $config->get('kitsu_username');

		$auth = $this->model->authenticate($username, $password);

		return $this->storeAuth($auth);
	}

	/**
	 * Make the call to re-authenticate with the existing refresh token
	 *
	 * @param string $refreshToken
	 * @return boolean
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function reAuthenticate(?string $refreshToken): bool
	{
		$refreshToken ??= $this->getAuthToken();

		if (empty($refreshToken))
		{
			return FALSE;
		}

		$auth = $this->model->reAuthenticate($refreshToken);

		return $this->storeAuth($auth);
	}

	/**
	 * Check whether the current user is authenticated
	 *
	 * @return boolean
	 */
	public function isAuthenticated(): bool
	{
		return ($this->getAuthToken() !== NULL);
	}

	/**
	 * Clear authentication values
	 *
	 * @return void
	 */
	public function logout(): void
	{
		$this->segment->clear();
	}

	/**
	 * Retrieve the authentication token from the session
	 *
	 * @return string
	 */
	private function getAuthToken(): ?string
	{
		$now = time();

		if (PHP_SAPI === 'cli')
		{
			$token = $this->cacheGet(K::AUTH_TOKEN_CACHE_KEY, NULL);
			$refreshToken = $this->cacheGet(K::AUTH_TOKEN_REFRESH_CACHE_KEY, NULL);
			$expireTime = $this->cacheGet(K::AUTH_TOKEN_EXP_CACHE_KEY);
			$isExpired = $now > $expireTime;
		}
		else
		{
			$token = $this->segment->get('auth_token', NULL);
			$refreshToken = $this->segment->get('refresh_token', NULL);
			$isExpired = $now > $this->segment->get('auth_token_expires', $now + 5000);
		}

		// Attempt to re-authenticate with refresh token
		/* if ($isExpired === TRUE && $refreshToken !== NULL)
		{
			if ($this->reAuthenticate($refreshToken) !== NULL)
			{
				return (PHP_SAPI === 'cli')
					? $this->cacheGet(K::AUTH_TOKEN_CACHE_KEY, NULL)
					: $this->segment->get('auth_token', NULL);
			}

			return NULL;
		}*/

		return $token;
	}

	private function getRefreshToken(): ?string
	{
		return (PHP_SAPI === 'cli')
			? $this->cacheGet(K::AUTH_TOKEN_REFRESH_CACHE_KEY, NULL)
			: $this->segment->get('refresh_token');
	}

	private function storeAuth(bool $auth): bool
	{
		if (FALSE !== $auth)
		{
			// Set the token in the cache for command line operations
			$cacheItem = $this->cache->getItem(K::AUTH_TOKEN_CACHE_KEY);
			$cacheItem->set($auth['access_token']);
			$cacheItem->save();

			// Set the token expiration in the cache
			$expire_time = $auth['created_at'] + $auth['expires_in'];
			$cacheItem = $this->cache->getItem(K::AUTH_TOKEN_EXP_CACHE_KEY);
			$cacheItem->set($expire_time);
			$cacheItem->save();

			// Set the refresh token in the cache
			$cacheItem = $this->cache->getItem(K::AUTH_TOKEN_REFRESH_CACHE_KEY);
			$cacheItem->set($auth['refresh_token']);
			$cacheItem->save();

			// Set the session values
			$this->segment->set('auth_token', $auth['access_token']);
			$this->segment->set('auth_token_expires', $expire_time);
			$this->segment->set('refresh_token', $auth['refresh_token']);
			return TRUE;
		}

		return FALSE;
	}

	private function cacheGet(string $key, $default = NULL)
	{
		$cacheItem = $this->cache->getItem($key);
		if ( ! $cacheItem->isHit())
		{
			return $default;
		}

		return $cacheItem->get();
	}
}
// End of KitsuAuth.php