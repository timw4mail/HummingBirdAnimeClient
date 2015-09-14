<?php
/**
 * Routing logic
 */
namespace AnimeClient\Base;

use \Aura\Web\Request;
use \Aura\Web\Response;

/**
 * Basic routing/ dispatch
 */
class Router extends RoutingBase {

	/**
	 * The route-matching object
	 * @var object $router
	 */
	protected $router;

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
	 * @param Config $config
	 * @param Router $router
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->router = $container->get('aura-router');
		$this->request = $container->get('request');
		$this->web = [$this->request, $container->get('response')];

		$this->output_routes = $this->_setup_routes();
	}

	/**
	 * Get the current route object, if one matches
	 *
	 * @return object
	 */
	public function get_route()
	{
		$error_handler = $this->container->get('error-handler');

		$raw_route = $this->request->server->get('PATH_INFO');
		$route_path = "/" . trim($raw_route, '/');

		$error_handler->addDataTable('Route Info', [
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
		$error_handler = $this->container->get('error-handler');

		if (is_null($route))
		{
			$route = $this->get_route();
			$error_handler->addDataTable('route_args', (array)$route);
		}

		if ( ! $route)
		{
			$failure = $this->router->getFailedRoute();
			$error_handler->addDataTable('failed_route', (array)$failure);
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

		$controller = new $controller_name($this->container);

		// Run the appropriate controller method
		$error_handler->addDataTable('controller_args', $params);
		call_user_func_array([$controller, $action_method], $params);
	}

	/**
	 * Get the type of route, to select the current controller
	 *
	 * @return string
	 */
	public function get_controller()
	{
		$error_handler = $this->container->get('error-handler');
		$route_type = $this->__get('default_list');

		$host = $this->request->server->get("HTTP_HOST");
		$request_uri = $this->request->server->get('PATH_INFO');

		$path = trim($request_uri, '/');

		$segments = explode('/', $path);
		$controller = reset($segments);

		//$controller_class = '\\AnimeClient\\Controller\\' . ucfirst($controller);

		return $controller;
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return array
	 */
	public function _setup_routes()
	{
		$output_routes = [];

		$route_type = $this->get_controller();

		// Return early if invalid route array
		if ( ! array_key_exists($route_type, $this->routes)) return [];

		$applied_routes = array_merge($this->routes[$route_type], $this->routes['common']);

		// Add routes
		foreach($applied_routes as $name => &$route)
		{
			$path = $route['path'];
			unset($route['path']);

			$controller_class = '\\AnimeClient\\Controller\\' . ucfirst($route_type);

			// Prepend the controller to the route parameters
			array_unshift($route['action'], $controller_class);

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