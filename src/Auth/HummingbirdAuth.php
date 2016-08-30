<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Auth;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\AnimeClient;

/**
 * Hummingbird API Authentication
 */
class HummingbirdAuth {

	use \Aviat\Ion\Di\ContainerAware;

	/**
	 * Anime API Model
	 *
	 * @var \Aviat\AnimeClient\Model\API
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
		$this->segment = $container->get('session')
			->getSegment(AnimeClient::SESSION_SEGMENT);
		$this->model = $container->get('api-model');
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
		$username = $this->container->get('config')
			->get('hummingbird_username');
		$auth_token = $this->model->authenticate($username, $password);

		if (FALSE !== $auth_token)
		{
			$this->segment->set('auth_token', $auth_token);
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
// End of HummingbirdAuth.php