<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use Aviat\AnimeClient\API\{
	CacheTrait,
	Kitsu as K
};
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Exception;

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
	private $model;

	/**
	 * Session object
	 *
	 * @var \Aura\Session\Segment
	 */
	private $segment;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->setCache($container->get('cache'));
		$this->segment = $container->get('session')
			->getSegment(SESSION_SEGMENT);
		$this->model = $container->get('kitsu-model');
	}

	/**
	 * Make the appropriate authentication call,
	 * and save the resulting auth token if successful
	 *
	 * @param  string $password
	 * @return boolean
	 */
	public function authenticate(string $password): bool
	{
		$config = $this->container->get('config');
		$username = $config->get(['kitsu_username']);

		// try
		{
			$auth = $this->model->authenticate($username, $password);
		}
		/* catch (Exception $e)
		{
			return FALSE;
		}*/


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


	/**
	 * Make the call to re-authenticate with the existing refresh token
	 *
	 * @param string $token
	 * @return boolean
	 */
	public function reAuthenticate(string $token): bool
	{
		try
		{
			$auth = $this->model->reAuthenticate($token);
		}
		catch (Exception $e)
		{
			return FALSE;
		}

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


	/**
	 * Check whether the current user is authenticated
	 *
	 * @return boolean
	 */
	public function isAuthenticated(): bool
	{
		return ($this->get_auth_token() !== FALSE);
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
	 * @return string|false
	 */
	public function get_auth_token()
	{
		$token = $this->segment->get('auth_token', FALSE);
		$refreshToken = $this->segment->get('refresh_token', FALSE);
		$isExpired = time() > $this->segment->get('auth_token_expires', 0);

		// Attempt to re-authenticate with refresh token
		if ($isExpired && $refreshToken)
		{
			if ($this->reAuthenticate($refreshToken))
			{
				return $this->segment->get('auth_token', FALSE);
			}

			return FALSE;
		}

		return $token;
	}
}
// End of KitsuAuth.php