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
	 * Route configuration options
	 * @var array
	 */
	protected $routeConfig;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$baseRoutes = $this->config->get('routes');
		$this->routes = $baseRoutes['routes'];
		$this->routeConfig = $baseRoutes['route_config'];
	}

	/**
	 * Retreive the appropriate value for the routing key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$routingConfig =& $this->routeConfig;

		if (array_key_exists($key, $routingConfig))
		{
			return $routingConfig[$key];
		}
	}

	/**
	 * Get the current url path
	 *
	 * @return string
	 */
	public function path()
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
	 *
	 * @return array
	 */
	public function segments()
	{
		$path = $this->path();
		return explode('/', $path);
	}

	/**
	 * Get a segment of the current url
	 *
	 * @param int $num
	 * @return string|null
	 */
	public function getSegment($num)
	{
		$segments = $this->segments();
		return (array_key_exists($num, $segments)) ? $segments[$num] : NULL;
	}

	/**
	 * Retrieve the last url segment
	 *
	 * @return string
	 */
	public function lastSegment()
	{
		$segments = $this->segments();
		return end($segments);
	}
}
// End of RoutingBase.php