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

use const Aviat\AnimeClient\SESSION_SEGMENT;

use Aviat\AnimeClient\API\{
	CacheTrait,
	Kitsu as K
};
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\Event;

use Psr\SimpleCache\InvalidArgumentException;

use Throwable;

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
	 * @throws Throwable|InvalidArgumentException
	 */
	public function reAuthenticate(?string $refreshToken): bool
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
	 *
	 * @return boolean
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
	 */
	public function getAuthToken(): ?string
	{
		if (PHP_SAPI === 'cli')
		{
			return $this->segment->get('auth_token', NULL)
				?? $this->cache->get(K::AUTH_TOKEN_CACHE_KEY, NULL);
		}

		return $this->segment->get('auth_token', NULL);
	}

	/**
	 * Retrieve the refresh token
	 *
	 * @return string|null
	 * @throws InvalidArgumentException
	 */
	private function getRefreshToken(): ?string
	{
		if (PHP_SAPI === 'cli')
		{
			return $this->segment->get('refresh_token')
				?? $this->cache->get(K::AUTH_TOKEN_REFRESH_CACHE_KEY, NULL);
		}

		return $this->segment->get('refresh_token');
	}

	/**
	 * Save the new authentication information
	 *
	 * @param $auth
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	private function storeAuth($auth): bool
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