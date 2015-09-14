<?php

namespace AnimeClient\Base;

class RoutingBase {
	/**
	 * Injection Container
	 * @var Container $container
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
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->routes = $this->config->__get('routes');
	}

	/**
	 * Retreive the appropriate value for the routing key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$routing_config = $this->config->routing;

		if (array_key_exists($key, $routing_config))
		{
			return $routing_config[$key];
		}
	}
}
// End of RoutingBase.php