<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\API\{
	CacheTrait,
	Kitsu as K
};
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Exception;

/**
 * Kitsu API Authentication
 */
class Auth {
	use CacheTrait;
	use ContainerAware;

	/**
	 * Anime API Model
	 *
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $model;

	/**
	 * Session object
	 *
	 * @var Aura\Session\Segment
	 */
	protected $segment;

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
	public function authenticate($password)
	{
		$config = $this->container->get('config');
		$username = $config->get(['kitsu_username']);

		try
		{
			$auth = $this->model->authenticate($username, $password);
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

			$this->segment->set('auth_token', $auth['access_token']);
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check whether the current user is authenticated
	 *
	 * @return boolean
	 */
	public function is_authenticated()
	{
		return ($this->get_auth_token() !== FALSE);
	}

	/**
	 * Clear authentication values
	 *
	 * @return void
	 */
	public function logout()
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
		return $this->segment->get('auth_token', FALSE);
	}
}
// End of KitsuAuth.php