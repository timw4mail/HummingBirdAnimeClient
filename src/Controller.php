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
namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\View\HttpView;
use Aviat\Ion\View\HtmlView;
use Aviat\Ion\View\JsonView;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * Controller base, defines output methods
 *
 * @property Response object $response
 */
class Controller {

	use \Aviat\Ion\Di\ContainerAware;

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
	protected $base_data = [
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
		$this->base_data['url'] = $auraUrlGenerator;
		$this->base_data['urlGenerator'] = $urlGenerator;
		$this->base_data['auth'] = $container->get('auth');
		$this->base_data['config'] = $this->config;
		$this->urlGenerator = $urlGenerator;

		$session = $container->get('session');
		$this->session = $session->getSegment(AnimeClient::SESSION_SEGMENT);

		// Set a 'previous' flash value for better redirects
		$server_params = $this->request->getServerParams();
		if (array_key_exists('HTTP_REFERER', $server_params))
		{
			$this->session->setFlash('previous', $server_params['HTTP_REFERER']);
		}

		// Set a message box if available
		$this->base_data['message'] = $this->session->getFlash('message');
	}

	/**
	 * Redirect to the default controller/url from an empty path
	 *
	 * @return void
	 */
	public function redirect_to_default()
	{
		$default_type = $this->config->get(['routes', 'route_config', 'default_list']);
		$this->redirect($this->urlGenerator->default_url($default_type), 303);
	}

	/**
	 * Redirect to the previous page
	 *
	 * @return void
	 */
	public function redirect_to_previous()
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
	public function set_session_redirect($url = NULL)
	{
		$server_params = $this->request->getServerParams();

		if ( ! array_key_exists('HTTP_REFERER', $server_params))
		{
			return;
		}

		$util = $this->container->get('util');
		$double_form_page = $server_params['HTTP_REFERER'] === $this->request->getUri();

		// Don't attempt to set the redirect url if
		// the page is one of the form type pages,
		// and the previous page is also a form type page_segments
		if ($double_form_page)
		{
			return;
		}

		if (is_null($url))
		{
			$url = ($util->is_view_page())
				? $this->request->url->get()
				: $server_params['HTTP_REFERER'];
		}

		$this->session->set('redirect_url', $url);
	}

	/**
	 * Redirect to the url previously set in the  session
	 *
	 * @return void
	 */
	public function session_redirect()
	{
		$target = $this->session->get('redirect_url');
		if (empty($target))
		{
			$this->not_found();
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
	 * @return object
	 */
	public function __get($key)
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
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	protected function load_partial($view, $template, array $data = [])
	{
		$router = $this->container->get('dispatcher');

		if (isset($this->base_data))
		{
			$data = array_merge($this->base_data, $data);
		}

		$route = $router->get_route();
		$data['route_path'] = ($route) ? $router->get_route()->path : "";


		$template_path = _dir($this->config->get('view_path'), "{$template}.php");

		if ( ! is_file($template_path))
		{
			throw new \InvalidArgumentException("Invalid template : {$template}");
		}

		return $view->render_template($template_path, (array)$data);
	}

	/**
	 * Render a template with header and footer
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array $data
	 * @return void
	 */
	protected function render_full_page($view, $template, array $data)
	{
		$view->appendOutput($this->load_partial($view, 'header', $data));

		if (array_key_exists('message', $data) && is_array($data['message']))
		{
			$view->appendOutput($this->load_partial($view, 'message', $data['message']));
		}

		$view->appendOutput($this->load_partial($view, $template, $data));
		$view->appendOutput($this->load_partial($view, 'footer', $data));
	}

	/**
	 * Show the login form
	 *
	 * @codeCoverageIgnore
	 * @param string $status
	 * @return void
	 */
	public function login($status = "")
	{
		$message = "";

		$view = new HtmlView($this->container);

		if ($status !== "")
		{
			$message = $this->show_message($view, 'error', $status);
		}

		// Set the redirect url
		$this->set_session_redirect();

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
	public function login_action()
	{
		$auth = $this->container->get('auth');
		$post = $this->request->getParsedBody();
		if ($auth->authenticate($post['password']))
		{
			return $this->session_redirect();
		}

		$this->login("Invalid username or password.");
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

		$this->redirect_to_default();
	}

	/**
	 * 404 action
	 *
	 * @return void
	 */
	public function not_found()
	{
		$this->outputHTML('404', [
			'title' => 'Sorry, page not found'
		], NULL, 404);
	}

	/**
	 * Display a generic error page
	 *
	 * @param int $http_code
	 * @param string $title
	 * @param string $message
	 * @param string $long_message
	 * @return void
	 */
	public function error_page($http_code, $title, $message, $long_message = "")
	{
		$this->outputHTML('error', [
			'title' => $title,
			'message' => $message,
			'long_message' => $long_message
		], NULL, $http_code);
	}

	/**
	 * Set a session flash variable to display a message on
	 * next page load
	 *
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	public function set_flash_message($message, $type = "info")
	{
		$this->session->setFlash('message', [
			'message_type' => $type,
			'message' => $message
		]);
	}

	/**
	 * Purges the API cache
	 *
	 * @return void
	 */
	public function clear_cache()
	{
		$this->cache->purge();
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
	protected function show_message($view, $type, $message)
	{
		return $this->load_partial($view, 'message', [
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
		$this->render_full_page($view, $template, $data);
	}

	/**
	 * Output a JSON Response
	 *
	 * @param mixed $data
	 * @param int $code - the http status code
	 * @return void
	 */
	protected function outputJSON($data = [], $code = 200)
	{
		$view = new JsonView($this->container);
		$view->setStatusCode($code);
		$view->setOutput($data);
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