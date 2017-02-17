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

namespace Aviat\AnimeClient;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\View\{HtmlView, HttpView, JsonView};
use InvalidArgumentException;

/**
 * Controller base, defines output methods
 *
 * @property Response object $response
 */
class Controller {

	use ContainerAware;

	/**
	 * Cache manager
	 * @var \Aviat\Ion\Cache\CacheInterface
	 */
	protected $cache;

	/**
	 * The global configuration object
	 * @var Aviat\Ion\ConfigInterface $config
	 */
	protected $config;

	/**
	 * Request object
	 * @var object $request
	 */
	protected $request;

	/**
	 * Response object
	 * @var object $response
	 */
	protected $response;

	/**
	 * The api model for the current controller
	 * @var object
	 */
	protected $model;

	/**
	 * Url generation class
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * Session segment
	 * @var [type]
	 */
	protected $session;

	/**
	 * Common data to be sent to views
	 * @var array
	 */
	protected $baseData = [
		'url_type' => 'anime',
		'other_type' => 'manga',
		'menu_name' => ''
	];

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
	 * Redirect to the default controller/url from an empty path
	 *
	 * @return void
	 */
	public function redirectToDefaultRoute()
	{
		$defaultType = $this->config->get(['routes', 'route_config', 'default_list']);
		$this->redirect($this->urlGenerator->defaultUrl($defaultType), 303);
	}

	/**
	 * Redirect to the previous page
	 *
	 * @return void
	 */
	public function redirectToPrevious()
	{
		$previous = $this->session->getFlash('previous');
		$this->redirect($previous, 303);
	}

	/**
	 * Set the current url in the session as the target of a future redirect
	 *
	 * @param string|null $url
	 * @return void
	 */
	public function setSessionRedirect($url = NULL)
	{
		$serverParams = $this->request->getServerParams();

		if ( ! array_key_exists('HTTP_REFERER', $serverParams))
		{
			return;
		}

		$util = $this->container->get('util');
		$doubleFormPage = $serverParams['HTTP_REFERER'] === $this->request->getUri();

		// Don't attempt to set the redirect url if
		// the page is one of the form type pages,
		// and the previous page is also a form type page_segments
		if ($doubleFormPage)
		{
			return;
		}

		if (is_null($url))
		{
			$url = $util->isViewPage()
				? $this->request->url->get()
				: $serverParams['HTTP_REFERER'];
		}

		$this->session->set('redirect_url', $url);
	}

	/**
	 * Redirect to the url previously set in the  session
	 *
	 * @return void
	 */
	public function sessionRedirect()
	{
		$target = $this->session->get('redirect_url');
		if (empty($target))
		{
			$this->notFound();
		}
		else
		{
			$this->redirect($target, 303);
			$this->session->set('redirect_url', NULL);
		}
	}

	/**
	 * Get a class member
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		$allowed = ['response', 'config'];

		if (in_array($key, $allowed))
		{
			return $this->$key;
		}

		return NULL;
	}

	/**
	 * Get the string output of a partial template
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @return string
	 */
	protected function loadPartial($view, $template, array $data = [])
	{
		$router = $this->container->get('dispatcher');

		if (isset($this->baseData))
		{
			$data = array_merge($this->baseData, $data);
		}

		$route = $router->getRoute();
		$data['route_path'] = $route ? $router->getRoute()->path : '';


		$templatePath = _dir($this->config->get('view_path'), "{$template}.php");

		if ( ! is_file($templatePath))
		{
			throw new InvalidArgumentException("Invalid template : {$template}");
		}

		return $view->renderTemplate($templatePath, (array)$data);
	}

	/**
	 * Render a template with header and footer
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array $data
	 * @return void
	 */
	protected function renderFullPage($view, $template, array $data)
	{
		$view->appendOutput($this->loadPartial($view, 'header', $data));

		if (array_key_exists('message', $data) && is_array($data['message']))
		{
			$view->appendOutput($this->loadPartial($view, 'message', $data['message']));
		}

		$view->appendOutput($this->loadPartial($view, $template, $data));
		$view->appendOutput($this->loadPartial($view, 'footer', $data));
	}

	/**
	 * Show the login form
	 *
	 * @codeCoverageIgnore
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
			return $this->sessionRedirect();
		}

		$this->setFlashMessage('Invalid username or password.');
		$this->redirect($this->urlGenerator->url('login'), 303);
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

	/**
	 * 404 action
	 *
	 * @return void
	 */
	public function notFound()
	{
		$this->outputHTML('404', [
			'title' => 'Sorry, page not found'
		], NULL, 404);
	}

	/**
	 * Display a generic error page
	 *
	 * @param int $httpCode
	 * @param string $title
	 * @param string $message
	 * @param string $long_message
	 * @return void
	 */
	public function errorPage($httpCode, $title, $message, $long_message = "")
	{
		$this->outputHTML('error', [
			'title' => $title,
			'message' => $message,
			'long_message' => $long_message
		], NULL, $httpCode);
	}

	/**
	 * Set a session flash variable to display a message on
	 * next page load
	 *
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	public function setFlashMessage($message, $type = "info")
	{
		static $messages;
		
		if (!$messages)
		{
			$messages = [];
		}
		
		$messages[] = [
			'message_type' => $type,
			'message' => $message
		];
		
		$this->session->setFlash('message', $messages);
	}

	/**
	 * Purges the API cache
	 *
	 * @return void
	 */
	public function clearCache()
	{
		$this->cache->clear();
		$this->outputHTML('blank', [
			'title' => 'Cache cleared'
		], NULL, 200);
	}

	/**
	 * Add a message box to the page
	 *
	 * @codeCoverageIgnore
	 * @param HtmlView $view
	 * @param string $type
	 * @param string $message
	 * @return string
	 */
	protected function showMessage($view, $type, $message)
	{
		return $this->loadPartial($view, 'message', [
			'message_type' => $type,
			'message'  => $message
		]);
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @param string $template
	 * @param array $data
	 * @param HtmlView|null $view
	 * @param int $code
	 * @return void
	 */
	protected function outputHTML($template, array $data = [], $view = NULL, $code = 200)
	{
		if (is_null($view))
		{
			$view = new HtmlView($this->container);
		}

		$view->setStatusCode($code);
		$this->renderFullPage($view, $template, $data);
	}

	/**
	 * Output a JSON Response
	 *
	 * @param mixed $data
	 * @param int $code - the http status code
	 * @return void
	 */
	protected function outputJSON($data = 'Empty response', int $code = 200)
	{
		(new JsonView($this->container))
			->setStatusCode($code)
			->setOutput($data)
			->send();
	}

	/**
	 * Redirect to the selected page
	 *
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	protected function redirect($url, $code)
	{
		$http = new HttpView($this->container);
		$http->redirect($url, $code);
	}
}
// End of BaseController.php