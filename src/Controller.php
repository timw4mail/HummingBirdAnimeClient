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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use function Aviat\Ion\_dir;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\View\{HtmlView, HttpView, JsonView};
use InvalidArgumentException;

/**
 * Controller base, defines output methods
 *
 * @property $response Response object
 */
class Controller {

	use ContainerAware;

	/**
	 * Cache manager
	 * @var \Psr\Cache\CacheItemPoolInterface
	 */
	protected $cache;

	/**
	 * The global configuration object
	 * @var \Aviat\Ion\ConfigInterface $config
	 */
	public $config;

	/**
	 * Request object
	 * @var object $request
	 */
	protected $request;

	/**
	 * Response object
	 * @var object $response
	 */
	public $response;

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
	 * Aura url generator
	 * @var \Aura\Router\Generator
	 */
	protected $url;

	/**
	 * Session segment
	 * @var \Aura\Session\Segment
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
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
		if (array_key_exists('HTTP_REFERER', $serverParams) && false === stripos($serverParams['HTTP_REFERER'], 'login'))
		{
			$this->session->setFlash('previous', $serverParams['HTTP_REFERER']);
		}

		// Set a message box if available
		$this->baseData['message'] = $this->session->getFlash('message');
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function setSessionRedirect(string $url = NULL)
	{
		$serverParams = $this->request->getServerParams();

		if ( ! array_key_exists('HTTP_REFERER', $serverParams))
		{
			return;
		}

		$util = $this->container->get('util');
		$doubleFormPage = $serverParams['HTTP_REFERER'] === $this->request->getUri();
		$isLoginPage = (bool) strpos($serverParams['HTTP_REFERER'], 'login');

		// Don't attempt to set the redirect url if
		// the page is one of the form type pages,
		// and the previous page is also a form type page_segments
		if ($doubleFormPage || $isLoginPage)
		{
			return;
		}

		if (null === $url)
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
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
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
	 * Get the string output of a partial template
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array $data
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return string
	 */
	protected function loadPartial($view, string $template, array $data = [])
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
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	protected function renderFullPage($view, string $template, array $data)
	{
		$csp = [
			"default-src 'self'",
			"object-src 'none'",
			"child-src 'none'",
		];

		$view->addHeader('Content-Security-Policy', implode('; ', $csp));
		$view->appendOutput($this->loadPartial($view, 'header', $data));

		if (array_key_exists('message', $data) && is_array($data['message']))
		{
			$view->appendOutput($this->loadPartial($view, 'message', $data['message']));
		}

		$view->appendOutput($this->loadPartial($view, $template, $data));
		$view->appendOutput($this->loadPartial($view, 'footer', $data));
	}

	/**
	 * 404 action
	 *
	 * @param string $title
	 * @param string $message
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function notFound(
		string $title = 'Sorry, page not found',
		string $message = 'Page Not Found'
		)
	{
		$this->outputHTML('404', [
			'title' => $title,
			'message' => $message,
		], NULL, 404);
	}

	/**
	 * Display a generic error page
	 *
	 * @param int $httpCode
	 * @param string $title
	 * @param string $message
	 * @param string $long_message
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function errorPage(int $httpCode, string $title, string $message, string $long_message = "")
	{
		$this->outputHTML('error', [
			'title' => $title,
			'message' => $message,
			'long_message' => $long_message
		], NULL, $httpCode);
	}

	/**
	 * Redirect to the default controller/url from an empty path
	 *
	 * @return void
	 */
	public function redirectToDefaultRoute()
	{
		$defaultType = $this->config->get(['routes', 'route_config', 'default_list']) ?? 'anime';
		$this->redirect($this->urlGenerator->defaultUrl($defaultType), 303);
	}

	/**
	 * Set a session flash variable to display a message on
	 * next page load
	 *
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	public function setFlashMessage(string $message, string $type = "info")
	{
		static $messages;

		if ( ! $messages)
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
	 * Helper for consistent page titles
	 *
	 * @param string[] ...$parts Title segments
	 * @return string
	 */
	public function formatTitle(string ...$parts) : string
	{
		return implode(' &middot; ', $parts);
	}

	/**
	 * Add a message box to the page
	 *
	 * @param HtmlView $view
	 * @param string $type
	 * @param string $message
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return string
	 */
	protected function showMessage($view, string $type, string $message): string
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
	 * @throws InvalidArgumentException
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	protected function outputHTML(string $template, array $data = [], $view = NULL, int $code = 200)
	{
		if (null === $view)
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
	 * @throws DoubleRenderException
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
	protected function redirect(string $url, int $code)
	{
		$http = new HttpView($this->container);
		$http->redirect($url, $code);
	}
}
// End of BaseController.php