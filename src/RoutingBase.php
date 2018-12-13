<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\StringWrapper;

/**
 * Base for routing/url classes
 */
class RoutingBase {

	use StringWrapper;

	/**
	 * Injection Container
	 * @var ContainerInterface $container
	 */
	protected $container;

	/**
	 * Config Object
	 * @var \Aviat\Ion\Config
	 */
	protected $config;

	/**
	 * Routing array
	 * @var array
	 */
	protected $routes;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @throws \Aviat\Ion\Exception\ConfigException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->routes = $this->config->get('routes');
	}

	/**
	 * Get the current url path
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return string
	 */
	public function path(): string
	{
		$request = $this->container->get('request');
		$path = $request->getUri()->getPath();
		$cleanedPath = $this->string($path)
			->replace('%20', '')
			->trim()
			->trimRight('/')
			->ensureLeft('/');

		return (string)$cleanedPath;
	}

	/**
	 * Get the url segments
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return array
	 */
	public function segments(): array
	{
		$path = $this->path();
		return explode('/', $path);
	}

	/**
	 * Get a segment of the current url
	 *
	 * @param int $num
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return string|null
	 */
	public function getSegment($num): ?string
	{
		$segments = $this->segments();
		return $segments[$num] ?? NULL;
	}

	/**
	 * Retrieve the last url segment
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return string
	 */
	public function lastSegment(): string
	{
		$segments = $this->segments();
		return end($segments);
	}
}
// End of RoutingBase.php