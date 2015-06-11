<?php

use Aura\Router\RouterFactory;

/**
 * Basic routing/ dispatch
 */
class Router {

	/**
	 * The route-matching object
	 * @var object $router
	 */
	protected $router;

	/**
	 * The global configuration object
	 * @var object $config
	 */
	protected $config;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $config;
		$this->config = $config;

		$router_factory = new RouterFactory();
		$router = $router_factory->newInstance();
		$this->router = $router_factory->newInstance();

		$this->_setup_routes();
	}

	/**
	 * Get the current route object, if one matches
	 *
	 * @return object
	 */
	public function get_route()
	{
		global $defaultHandler;

		$raw_route = $_SERVER['REQUEST_URI'];
		$route_path = str_replace([$this->config->anime_path, $this->config->manga_path], '', $raw_route);
		$route_path = "/" . trim($route_path, '/');

		$defaultHandler->addDataTable('Route Info', [
			'route_path' => $route_path
		]);

		$route = $this->router->match($route_path, $_SERVER);

		return $route;
	}

	/**
	 * Handle the current route
	 *
	 * @param [object] $route
	 * @return void
	 */
	public function dispatch($route = NULL)
	{
		global $defaultHandler;

		if (is_null($route))
		{
			$route = $this->get_route();
		}

		if ( ! $route)
		{
			$failure = $this->router->getFailedRoute();
			$defaultHandler->addDataTable('failed_route', (array)$failure);

			$controller_name = 'BaseController';
			$action_method = 'outputHTML';
			$params = [
				'template' => '404',
				'data' => [
					'title' => 'Page Not Found'
				]
			];
		}
		else
		{
			$controller_name = $route->params['controller'];
			$action_method = $route->params['action'];
			$params = (isset($route->params['params'])) ? $route->params['params'] : [];
		}

		$controller = new $controller_name();

		// Run the appropriate controller method
		call_user_func_array([$controller, $action_method], $params);
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return void
	 */
	private function _setup_routes()
	{
		$route_type = "anime";

		if ($this->config->manga_host !== "" && strpos($_SERVER['HTTP_HOST'], $this->config->manga_host) !== FALSE)
		{
			$route_type = "manga";
		}
		else if ($this->config->manga_path !== "" && strpos($_SERVER['REQUEST_URI'], $this->config->manga_path) !== FALSE)
		{
			$route_type = "manga";
		}

		$routes = $this->config->routes;

		// Add routes by the configuration file
		foreach($routes[$route_type] as $name => $route)
		{
			$path = $route['path'];
			unset($route['path']);
			$this->router->add($name, $path)->addValues($route);
		}
	}
}
// End of Router.php