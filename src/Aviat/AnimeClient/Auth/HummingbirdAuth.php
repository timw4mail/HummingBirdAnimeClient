<?php

namespace Aviat\AnimeClient\Auth;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

/**
 * Hummingbird API Authentication
 */
class HummingbirdAuth {

	use \Aviat\Ion\Di\ContainerAware;

	/**
	 * Anime API Model
	 *
	 * @var AnimeModel
	 */
	protected $model;

	/**
	 * Session object
	 *
	 * @var Aura\Session\Segment
	 */
	protected $session;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->session = $container->get('session')
			->getSegment(__NAMESPACE__);
		$this->model = new AnimeModel($container);
	}

	/**
	 * Make the appropriate authentication call,
	 * and save the resulting auth token if successful
	 *
	 * @param  string $username
	 * @param  string $password
	 * @return boolean
	 */
	public function authenticate($username, $password)
	{
		return $this->model->authenticate();
	}

	/**
	 * Retrieve the authentication token from the session
	 *
	 * @return string
	 */
	public function get_auth_token()
	{
		return $this->session->get('auth_token');
	}

}
// End of HummingbirdAuth.php