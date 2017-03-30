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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use function Aviat\Ion\_dir;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\View\{HtmlView, HttpView, JsonView};
use InvalidArgumentException;

/**
 * Controller base, defines output methods
 *
 * @property Response object $response
 */
class Controller {
	use ControllerTrait;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$auraUrlGenerator = $container->get('aura-router')->getGenerator();
		$urlGenerator = $container->get('url-generator');
		$this->cache =  $container->get('cache');
		$this->config = $container->get('config');
		$this->request = $container->get('request');
		$this->response = $container->get('response');

		$this->baseData = array_merge((array)$this->baseData, [
			'url' => $auraUrlGenerator,
			'urlGenerator' => $urlGenerator,
			'auth' => $container->get('auth'),
			'config' => $this->config
		]);

		$this->url = $auraUrlGenerator;
		$this->urlGenerator = $urlGenerator;

		$session = $container->get('session');
		$this->session = $session->getSegment(SESSION_SEGMENT);

		// Set a 'previous' flash value for better redirects
		$serverParams = $this->request->getServerParams();
		if (array_key_exists('HTTP_REFERER', $serverParams))
		{
			$this->session->setFlash('previous', $serverParams['HTTP_REFERER']);
		}

		// Set a message box if available
		$this->baseData['message'] = $this->session->getFlash('message');
	}

	/**
	 * Show the user profile page
	 *
	 * @return void
	 */
	public function me()
	{
		$username = $this->config->get(['kitsu_username']);
		$model = $this->container->get('kitsu-model');
		$data = $model->getUserData($username);
		$included = JsonAPI::lightlyOrganizeIncludes($data['included']);
		$relationships = JsonAPI::fillRelationshipsFromIncludes($data['data']['relationships'], $included);
		$this->outputHTML('me', [
			'title' => 'About' . $this->config->get('whose_list'),
			'attributes' => $data['data']['attributes'],
			'relationships' => $relationships,
			'included' => $included
		]);
	}

	/**
	 * Show the login form
	 *
	 * @param string $status
	 * @return void
	 */
	public function login(string $status = '')
	{
		$message = '';

		$view = new HtmlView($this->container);

		if ($status !== '')
		{
			$message = $this->showMessage($view, 'error', $status);
		}

		// Set the redirect url
		$this->setSessionRedirect();

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message
		], $view);
	}

	/**
	 * Attempt login authentication
	 *
	 * @return void
	 */
	public function loginAction()
	{
		$auth = $this->container->get('auth');
		$post = $this->request->getParsedBody();
		if ($auth->authenticate($post['password']))
		{
			$this->sessionRedirect();
			return;
		}

		$this->setFlashMessage('Invalid username or password.');
		$this->redirect($this->url->generate('login'), 303);
	}

	/**
	 * Deauthorize the current user
	 *
	 * @return void
	 */
	public function logout()
	{
		$auth = $this->container->get('auth');
		$auth->logout();

		$this->redirectToDefaultRoute();
	}
}
// End of BaseController.php