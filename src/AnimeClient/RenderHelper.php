<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html;
use Aviat\AnimeClient\API\Kitsu\Auth;
use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A container for helper functions and data for rendering HTML output
 */
class RenderHelper {
	use ContainerAware;

	/**
	 * The authentication object
	 */
	public Auth $auth;

	/**
	 * The global configuration object
	 */
	public ConfigInterface $config;

	/**
	 * HTML component helper
	 */
	public Html\HelperLocator $component;

	/**
	 * HTML escaper
	 */
	public Html\Escaper $escape;

	/**
	 * HTML render helper
	 */
	public Html\HelperLocator $h;

	/**
	 * Request object
	 */
	protected ServerRequestInterface $request;

	/**
	 * Aura url generator
	 */
	protected \Aura\Router\Generator $url;

	/**
	 * Url generation class
	 */
	private UrlGenerator $urlGenerator;

	/**
	 * Routes that don't require a second navigation level
	 */
	private static array $formPages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout',
		'details',
		'character',
		'me',
	];

	public function __construct(ContainerInterface $container) {
		$this->setContainer($container);

		$this->auth = $container->get('auth');
		$this->component = $container->get('component-helper');
		$this->config = $container->get('config');
		$this->h = $container->get('html-helper');
		$this->escape = $this->h->escape();
		$this->request = $this->container->get('request');
		$this->url = $container->get('aura-router')->getGenerator();
		$this->urlGenerator = $container->get('url-generator');
	}

	/**
	 * Get the base url for css/js/images
	 */
	public function assetUrl(string ...$args): string
	{
		return $this->urlGenerator->assetUrl(...$args);
	}

	/**
	 * Full default path for the list pages
	 */
	public function defaultUrl(string $type): string
	{
		return $this->urlGenerator->defaultUrl($type);
	}

	/**
	 * Retrieve the last url segment
	 */
	public function lastSegment(): string
	{
		return $this->urlGenerator->lastSegment();
	}

	/**
	 * Generate a full url from a path
	 */
	public function urlFromPath(string $path): string
	{
		return $this->urlGenerator->url($path);
	}

	/**
	 * Generate a url from its name and parameters
	 */
	public function urlFromRoute(string $name, array $data = []): string
	{
		return $this->url->generate($name, $data);
	}

	/**
	 * Is the current user authenticated?
	 */
	public function isAuthenticated(): bool
	{
		return $this->auth->isAuthenticated();
	}

	/**
	 * Determine whether to show the sub-menu
	 */
	public function isViewPage(): bool
	{
		$url = $this->request->getUri();
		$pageSegments = explode('/', (string) $url);

		$intersect = array_intersect($pageSegments, self::$formPages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function isFormPage(): bool
	{
		return ! $this->isViewPage();
	}
}