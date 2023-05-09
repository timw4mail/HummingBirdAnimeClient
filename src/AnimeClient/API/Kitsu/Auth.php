<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aura\Session\Segment;

use Aviat\AnimeClient\API\CacheTrait;

use Aviat\AnimeClient\Kitsu as K;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Event;
use const Aviat\AnimeClient\SESSION_SEGMENT;

/**
 * Kitsu API Authentication
 */
final class Auth
{
	use CacheTrait;
	use ContainerAware;

	/**
	 * Anime API Model
	 */
	private Model $model;

	/**
	 * Session object
	 */
	private Segment $segment;

	/**
	 * Constructor
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
	 */
	public function reAuthenticate(?string $refreshToken = NULL): bool
	{
		$refreshToken ??= $this->getRefreshToken();

		if (empty($refreshToken))
		{
			return FALSE;
		}

		$auth = $this->model->reAuthenticate($refreshToken);

		return $this->storeAuth($auth);
	}

	/**
	 * Check whether the current user is authenticated
	 */
	public function isAuthenticated(): bool
	{
		return $this->getAuthToken() !== NULL;
	}

	/**
	 * Clear authentication values
	 */
	public function logout(): void
	{
		$this->segment->clear();
	}

	/**
	 * Retrieve the authentication token from the session
	 */
	public function getAuthToken(): ?string
	{
		if (PHP_SAPI === 'cli')
		{
			return $this->segment->get('auth_token')
				?? $this->cache->get(K::AUTH_TOKEN_CACHE_KEY);
		}

		return $this->segment->get('auth_token');
	}

	/**
	 * Retrieve the refresh token
	 */
	private function getRefreshToken(): ?string
	{
		if (PHP_SAPI === 'cli')
		{
			return $this->segment->get('refresh_token')
				?? $this->cache->get(K::AUTH_TOKEN_REFRESH_CACHE_KEY);
		}

		return $this->segment->get('refresh_token');
	}

	/**
	 * Save the new authentication information
	 */
	private function storeAuth(array|false $auth): bool
	{
		if (FALSE !== $auth)
		{
			$expire_time = $auth['created_at'] + $auth['expires_in'];

			// Set the token in the cache for command line operations
			// Set the token expiration in the cache
			// Set the refresh token in the cache
			$saved = $this->cache->setMultiple([
				K::AUTH_TOKEN_CACHE_KEY => $auth['access_token'],
				K::AUTH_TOKEN_EXP_CACHE_KEY => $expire_time,
				K::AUTH_TOKEN_REFRESH_CACHE_KEY => $auth['refresh_token'],
			]);

			// Set the session values
			if ($saved)
			{
				$this->segment->set('auth_token', $auth['access_token']);
				$this->segment->set('auth_token_expires', $expire_time);
				$this->segment->set('refresh_token', $auth['refresh_token']);

				return TRUE;
			}
		}

		return FALSE;
	}
}

// End of KitsuAuth.php
