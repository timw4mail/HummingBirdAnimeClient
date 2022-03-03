<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Router\Generator;

use Aura\Session\Segment;
use Aviat\AnimeClient\API\Kitsu\Auth;
use Aviat\AnimeClient\Enum\EventType;
use Aviat\Ion\Di\{
	ContainerAware,
	ContainerInterface,
	Exception\ContainerException,
	Exception\NotFoundException
};
use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\View\{HtmlView, HttpView, JsonView};

use Aviat\Ion\{ConfigInterface, Event};
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use function Aviat\Ion\_dir;
use function is_array;

/**
 * Controller base, defines output methods
 */
class Controller
{
	use ContainerAware;

	/**
	 * The authentication object
	 */
	protected Auth $auth;

	/**
	 * Cache manager
	 */
	protected CacheInterface $cache;

	/**
	 * The global configuration object
	 */
	public ConfigInterface $config;

	/**
	 * Request object
	 */
	protected ServerRequestInterface $request;

	/**
	 * Url generation class
	 */
	protected UrlGenerator $urlGenerator;

	/**
	 * Aura url generator
	 */
	protected Generator $url;

	/**
	 * Session segment
	 */
	protected Segment $session;

	/**
	 * Common data to be sent to views
	 */
	protected array $baseData = [];

	/**
	 * Controller constructor.
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$auraUrlGenerator = $container->get('aura-router')->getGenerator();
		$session = $container->get('session');
		$urlGenerator = $container->get('url-generator');

		$this->auth = $container->get('auth');
		$this->cache = $container->get('cache');
		$this->config = $container->get('config');
		$this->request = $container->get('request');
		$this->session = $session->getSegment(SESSION_SEGMENT);
		$this->url = $auraUrlGenerator;
		$this->urlGenerator = $urlGenerator;

		$this->baseData = [
			'auth' => $container->get('auth'),
			'config' => $this->config,
			'menu_name' => '',
			'message' => $this->session->getFlash('message'), // Get message box data if it exists
			'other_type' => 'manga',
			'url' => $auraUrlGenerator,
			'url_type' => 'anime',
			'urlGenerator' => $urlGenerator,
		];

		// Set up 'global' events
		Event::on(EventType::CLEAR_CACHE, fn () => clearCache($this->cache));
		Event::on(EventType::RESET_CACHE_KEY, fn (string $key) => $this->cache->delete($key));
	}

	/**
	 * Set the current url in the session as the target of a future redirect
	 *
	 * @codeCoverageIgnore
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function setSessionRedirect(?string $url = NULL): void
	{
		$serverParams = $this->request->getServerParams();

		if ( ! array_key_exists('HTTP_REFERER', $serverParams))
		{
			return;
		}

		$util = $this->container->get('util');
		$doubleFormPage = $serverParams['HTTP_REFERER'] === $this->request->getUri();
		$isLoginPage = str_contains($serverParams['HTTP_REFERER'], 'login');

		// Don't attempt to set the redirect url if
		// the page is one of the form type pages,
		// and the previous page is also a form type
		if ($doubleFormPage || $isLoginPage)
		{
			return;
		}

		if (NULL === $url)
		{
			$url = $util->isViewPage()
				? (string) $this->request->getUri()
				: $serverParams['HTTP_REFERER'];
		}

		$this->session->set('redirect_url', $url);
	}

	/**
	 * Redirect to the url previously set in the  session
	 *
	 * If one is not set, redirect to default url
	 *
	 * @codeCoverageIgnore
	 * @throws InvalidArgumentException
	 */
	public function sessionRedirect(): void
	{
		$target = $this->session->get('redirect_url') ?? '/';

		$this->redirect($target, 303);
		$this->session->set('redirect_url', NULL);
	}

	/**
	 * Check if the current user is authenticated, else error and exit
	 * @codeCoverageIgnore
	 */
	protected function checkAuth(): void
	{
		if ( ! $this->auth->isAuthenticated())
		{
			$this->errorPage(
				403,
				'Forbidden',
				'You must <a href="/login">log in</a> to perform this action.'
			);
		}
	}

	/**
	 * Get the string output of a partial template
	 *
	 * @codeCoverageIgnore
	 */
	protected function loadPartial(HtmlView $view, string $template, array $data = []): string
	{
		$router = $this->container->get('dispatcher');

		if (isset($this->baseData))
		{
			$data = array_merge($this->baseData, $data);
		}

		$route = $router->getRoute();
		$data['route_path'] = $route !== FALSE ? $route->path : '';

		$templatePath = _dir($this->config->get('view_path'), "{$template}.php");

		if ( ! is_file($templatePath))
		{
			throw new InvalidArgumentException("Invalid template : {$template}");
		}

		return $view->renderTemplate($templatePath, $data);
	}

	/**
	 * Render a template with header and footer
	 *
	 * @codeCoverageIgnore
	 */
	protected function renderFullPage(HtmlView $view, string $template, array $data): HtmlView
	{
		$csp = [
			"default-src 'self' media.kitsu.io kitsu-production-media.s3.us-west-002.backblazeb2.com",
			"object-src 'none'",
			"child-src 'self' *.youtube.com polyfill.io",
		];

		$view->addHeader('Content-Security-Policy', implode('; ', $csp));
		$view->appendOutput($this->loadPartial($view, 'header', $data));

		if (array_key_exists('message', $data) && is_array($data['message']))
		{
			$view->appendOutput($this->loadPartial($view, 'message', $data['message']));
		}

		$view->appendOutput($this->loadPartial($view, $template, $data));
		$view->appendOutput($this->loadPartial($view, 'footer', $data));

		return $view;
	}

	/**
	 * 404 action
	 *
	 * @codeCoverageIgnore
	 * @throws InvalidArgumentException
	 */
	public function notFound(
		string $title = 'Sorry, page not found',
		string $message = 'Page Not Found'
	): void {
		$this->outputHTML('404', [
			'title' => $title,
			'message' => $message,
		], NULL, 404);

		exit();
	}

	/**
	 * Display a generic error page
	 *
	 * @codeCoverageIgnore
	 * @throws InvalidArgumentException
	 */
	public function errorPage(int $httpCode, string $title, string $message, string $longMessage = ''): void
	{
		$this->outputHTML('error', [
			'title' => $title,
			'message' => $message,
			'long_message' => $longMessage,
		], NULL, $httpCode);
	}

	/**
	 * Redirect to the default controller/url from an empty path
	 *
	 * @codeCoverageIgnore
	 * @throws InvalidArgumentException
	 */
	public function redirectToDefaultRoute(): void
	{
		$defaultType = $this->config->get('default_list');
		$this->redirect($this->urlGenerator->defaultUrl($defaultType), 303);
	}

	/**
	 * Set a session flash variable to display a message on
	 * next page load
	 *
	 * @codeCoverageIgnore
	 */
	public function setFlashMessage(string $message, string $type = 'info'): void
	{
		static $messages;

		if ( ! $messages)
		{
			$messages = [];
		}

		$messages[] = [
			'message_type' => $type,
			'message' => $message,
		];

		$this->session->setFlash('message', $messages);
	}

	/**
	 * Helper for consistent page titles
	 *
	 * @param string ...$parts Title segments
	 */
	public function formatTitle(string ...$parts): string
	{
		return implode(' &middot; ', $parts);
	}

	/**
	 * Add a message box to the page
	 *
	 * @codeCoverageIgnore
	 * @throws InvalidArgumentException
	 */
	protected function showMessage(HtmlView $view, string $type, string $message): string
	{
		return $this->loadPartial($view, 'message', [
			'message_type' => $type,
			'message' => $message,
		]);
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @codeCoverageIgnore
	 *@throws InvalidArgumentException
	 */
	protected function outputHTML(string $template, array $data = [], ?HtmlView $view = NULL, int $code = 200): void
	{
		if (NULL === $view)
		{
			$view = new HtmlView($this->container);
		}

		$view->setStatusCode($code);
		$this->renderFullPage($view, $template, $data)->send();
	}

	/**
	 * Output a JSON Response
	 *
	 * @codeCoverageIgnore
	 * @param int $code - the http status code
	 * @throws DoubleRenderException
	 */
	protected function outputJSON(mixed $data, int $code): void
	{
		(new JsonView())
			->setOutput($data)
			->setStatusCode($code)
			->send();
	}

	/**
	 * Redirect to the selected page
	 *
	 * @codeCoverageIgnore
	 */
	protected function redirect(string $url, int $code): void
	{
		try
		{
			(new HttpView())->redirect($url, $code)->send();
		}
		catch (\Throwable)
		{
		}
	}
}

// End of BaseController.php
