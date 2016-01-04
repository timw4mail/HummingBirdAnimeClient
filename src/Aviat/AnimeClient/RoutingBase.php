<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */
namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;

/**
 * Base for routing/url classes
 */
class RoutingBase {

	use \Aviat\Ion\StringWrapper;

	/**
	 * Injection Container
	 * @var ContainerInterface $container
	 */
	protected $container;

	/**
	 * Config Object
	 * @var Config
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
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->routes = $this->config->get('routes');
	}

	/**
	 * Retreive the appropriate value for the routing key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$routing_config = $this->config->get('routing');

		if (array_key_exists($key, $routing_config))
		{
			return $routing_config[$key];
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
		$path = $request->url->get(PHP_URL_PATH);
		$cleaned_path = $this->string($path)
			->trim()
			->trimRight('/')
			->ensureLeft('/');

		return (string)$cleaned_path;
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
	public function get_segment($num)
	{
		$segments = $this->segments();
		return (array_key_exists($num, $segments)) ? $segments[$num] : NULL;
	}

	/**
	 * Retrieve the last url segment
	 *
	 * @return string
	 */
	public function last_segment()
	{
		$segments = $this->segments();
		return end($segments);
	}
}
// End of RoutingBase.php