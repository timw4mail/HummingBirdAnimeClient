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
	 * Class wrapper for input superglobals
	 * @var object
	 */
	protected $request;

	/**
	 * Array containing request and response objects
	 * @var array $web
	 */
	protected $web;

	/**
	 * Routes added to router
	 * @var array $output_routes
	 */
	protected $output_routes;

	/**
	 * Constructor
	 *
	 * @param
	 */
	public function __construct(Config $config, \Aura\Router\Router $router, \Aura\Web\Request $request, \Aura\Web\Response $response)
	{
		$this->config = $config;
		$this->router = $router;
		$this->request = $request;
		$this->web = [$request, $response];

		$this->output_routes = $this->_setup_routes();
	}

	/**
	 * Get the current route object, if one matches
	 *
	 * @return object
	 */
	public function get_route()
	{
		global $defaultHandler;

		$raw_route = parse_url($this->request->server->get('REQUEST_URI'), \PHP_URL_PATH);
		$route_path = str_replace([$this->config->anime_path, $this->config->manga_path], '', $raw_route);
		$route_path = "/" . trim($route_path, '/');

		$defaultHandler->addDataTable('Route Info', [
			'route_path' => $route_path
		]);

		$route = $this->router->match($route_path, $_SERVER);

		return $route;
	}

	/**
	 * Get list of routes applied
	 *
	 * @return array
	 */
	public function get_output_routes()
	{
		return $this->output_routes;
	}

	/**
	 * Handle the current route
	 *
	 * @codeCoverageIgnore
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

			/*$controller_name = '\\AnimeClient\\BaseController';
			$action_method = 'outputHTML';
			$params = [
				'template' => '404',
				'data' => [
					'title' => 'Page Not Found'
				]
			];*/
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
	 * Get the type of route, to select the current controller
	 *
	 * @return string
	 */
	public function get_route_type()
	{
		$route_type = $this->config->default_list;

		$host = $this->request->server->get("HTTP_HOST");
		$request_uri = $this->request->server->get('REQUEST_URI');

		// Host-based controller selection
		if ($this->config->route_by === "host")
		{
			if (strtolower($host) === strtolower($this->config->anime_host))
			{
				$route_type = "anime";
			}

			if (strtolower($host) === strtolower($this->config->manga_host))
			{
				$route_type = "manga";
			}
		}

		// Path-based controller selection
		if ($this->config->route_by === "path")
		{
			$path = trim($request_uri, '/');

			if (stripos($path, trim($this->config->anime_path, '/')) === 0)
			{
				$route_type = "anime";
			}

			if (stripos($path, trim($this->config->manga_path, '/')) === 0)
			{
				$route_type = "manga";
			}
		}

		return $route_type;
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return array
	 */
	public function _setup_routes()
	{
		$route_map = [
			'anime' => '\\AnimeClient\\AnimeController',
			'manga' => '\\AnimeClient\\MangaController',
		];

		$output_routes = [];

		$route_type = $this->get_route_type();

		// Return early if invalid route array
		if ( ! array_key_exists($route_type, $this->config->routes)) return [];

		$applied_routes = array_merge($this->config->routes['common'], $this->config->routes[$route_type]);

		// Add routes
		foreach($applied_routes as $name => &$route)
		{
			$path = $route['path'];
			unset($route['path']);

			// Prepend the controller to the route parameters
			array_unshift($route['action'], $route_map[$route_type]);

			// Select the appropriate router method based on the http verb
			$add = (array_key_exists('verb', $route)) ? "add" . ucfirst(strtolower($route['verb'])) : "addGet";

			// Add the route to the router object
			if ( ! array_key_exists('tokens', $route))
			{
				$output_routes[] = $this->router->$add($name, $path)->addValues($route);
			}
			else
			{
				$tokens = $route['tokens'];
				unset($route['tokens']);

				$output_routes[] = $this->router->$add($name, $path)
					->addValues($route)
					->addTokens($tokens);
			}
		}

		return $output_routes;
	}
}
// End of Router.php