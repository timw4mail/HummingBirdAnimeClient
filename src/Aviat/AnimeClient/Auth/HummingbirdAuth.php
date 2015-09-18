<?php

namespace Aviat\AnimeClient\Auth;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

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
		$this->session = $container->get('sesion')
			->getSegment(__NAMESPACE__);
		$this->model = new AnimeModel($container);
	}

	public function authenticate($username, $password)
	{

	}

	public function get_auth_token()
	{

	}

}
// End of HummingbirdAuth.php