<?php
/**
 * Routing logic
 */

namespace AnimeClient;

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
	 * Array containing request and response objects
	 * @var array $web
	 */
	protected $web;

	/**
	 * Constructor
	 *
	 * @param
	 */
	public function __construct(Config $config, \Aura\Router\Router $router, \Aura\Web\Request $request, \Aura\Web\Response $response)
	{
		$this->config = $config;
		$this->router = $router;
		$this->web = [$request, $response];

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
			list($controller_name, $action_method) = $route->params['action'];
			$params = (isset($route->params['params'])) ? $route->params['params'] : [];

			if ( ! empty($route->tokens))
			{
				foreach($route->tokens as $key => $v)
				{
					if (array_key_exists($key, $route->params))
					{
						$params[$key] = $route->params[$key];
					}
				}
			}
		}

		$controller = new $controller_name($this->config, $this->web);

		// Run the appropriate controller method
		$defaultHandler->addDataTable('controller_args', $params);
		call_user_func_array([$controller, $action_method], $params);
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return void
	 */
	private function _setup_routes()
	{
		$route_map = [
			'anime' => '\\AnimeClient\\AnimeController',
			'manga' => '\\AnimeClient\\MangaController',
		];
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

		// Add routes
		foreach(['common', $route_type] as $key)
		{
			foreach($routes[$key] as $name => &$route)
			{
				$path = $route['path'];
				unset($route['path']);

				// Prepend the controller to the route parameters
				array_unshift($route['action'], $route_map[$route_type]);

				// Select the appropriate router method based on the http verb
				$add = (array_key_exists('verb', $route)) ? "add" . ucfirst(strtolower($route['verb'])) : "addGet";

				if ( ! array_key_exists('tokens', $route))
				{
					$this->router->$add($name, $path)->addValues($route);
				}
				else
				{
					$tokens = $route['tokens'];
					unset($route['tokens']);

					$this->router->$add($name, $path)
						->addValues($route)
						->addTokens($tokens);
				}
			}
		}
	}
}
// End of Router.php