<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Auth;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\API;

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
			->getSegment(__NAMESPACE__);
		$this->model = new API($container);
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