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

use Aura\Web\Request;
use Aura\Web\Response;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\AnimeClient;

/**
 * Basic routing/ dispatch
 */
class Dispatcher extends RoutingBase {

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
	 * Routes added to router
	 * @var array $output_routes
	 */
	protected $output_routes;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->router = $container->get('aura-router');
		$this->request = $container->get('request');

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

		$raw_route = $this->request->url->get(PHP_URL_PATH);
		$route_path = "/" . trim($raw_route, '/');

		$error_handler->addDataTable('Route Info', [
			'route_path' => $route_path
		]);

		return $this->router->match($route_path, $_SERVER);
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
	 * @param object|null $route
	 * @return void
	 */
	public function __invoke($route = NULL)
	{
		$error_handler = $this->container->get('error-handler');

		if (is_null($route))
		{
			$route = $this->get_route();
			$error_handler->addDataTable('route_args', (array)$route);
		}

		if($route)
		{
			$parsed = $this->process_route($route);
			$controller_name = $parsed['controller_name'];
			$action_method = $parsed['action_method'];
			$params = $parsed['params'];
		}
		else
		{
			// If not route was matched, return an appropriate http
			// error message
			$error_route = $this->get_error_params();
			$controller_name = AnimeClient::DEFAULT_CONTROLLER;
			$action_method = $error_route['action_method'];
			$params = $error_route['params'];
		}

		// Actually instantiate the controller
		$this->call($controller_name, $action_method, $params);
	}

	/**
	 * Parse out the arguments for the appropriate controller for
	 * the current route
	 *
	 * @param \Aura\Router\Route $route
	 * @return array
	 */
	protected function process_route($route)
	{
		if (array_key_exists('controller', $route->params))
		{
			$controller_name = $route->params['controller'];
		}
		else
		{
			throw new \LogicException("Missing controller");
		}

		// Get the full namespace for a controller if a short name is given
		if (strpos($controller_name, '\\') === FALSE)
		{
			$map = $this->get_controller_list();
			$controller_name = $map[$controller_name];
		}

		$action_method = (array_key_exists('action', $route->params))
			? $route->params['action']
			: AnimeClient::NOT_FOUND_METHOD;

		$params = (array_key_exists('params', $route->params))
			? $route->params['params']
			: [];

		if ( ! empty($route->tokens))
		{
			foreach ($route->tokens as $key => $v)
			{
				if (array_key_exists($key, $route->params))
				{
					$params[$key] = $route->params[$key];
				}
			}
		}

		return [
			'controller_name' => $controller_name,
			'action_method' => $action_method,
			'params' => $params
		];
	}

	/**
	 * Get the type of route, to select the current controller
	 *
	 * @return string
	 */
	public function get_controller()
	{
		$route_type = $this->__get('default_list');
		$request_uri = $this->request->url->get(PHP_URL_PATH);
		$path = trim($request_uri, '/');

		$segments = explode('/', $path);
		$controller = reset($segments);

		if (empty($controller))
		{
			$controller = $route_type;
		}

		return $controller;
	}

	/**
	 * Get the list of controllers in the default namespace
	 *
	 * @return array
	 */
	public function get_controller_list()
	{
		$default_namespace = AnimeClient::DEFAULT_CONTROLLER_NAMESPACE;
		$path = str_replace('\\', '/', $default_namespace);
		$path = trim($path, '/');
		$actual_path = realpath(\_dir(AnimeClient::SRC_DIR, $path));
		$class_files = glob("{$actual_path}/*.php");

		$controllers = [];

		foreach ($class_files as $file)
		{
			$raw_class_name = basename(str_replace(".php", "", $file));
			$path = strtolower(basename($raw_class_name));
			$class_name = trim($default_namespace . '\\' . $raw_class_name, '\\');

			$controllers[$path] = $class_name;
		}

		return $controllers;
	}

	/**
	 * Create the controller object and call the appropriate
	 * method
	 *
	 * @param  string $controller_name - The full namespace of the controller class
	 * @param  string $method
	 * @param  array  $params
	 * @return void
	 */
	protected function call($controller_name, $method, array $params)
	{
		$error_handler = $this->container->get('error-handler');

		$controller = new $controller_name($this->container);

		// Run the appropriate controller method
		$error_handler->addDataTable('controller_args', $params);
		call_user_func_array([$controller, $method], $params);
	}

	/**
	 * Get the appropriate params for the error page
	 * pased on the failed route
	 *
	 * @return array|false
	 */
	protected function get_error_params()
	{
		$failure = $this->router->getFailedRoute();
		$error_handler = $this->container->get('error-handler');
		$error_handler->addDataTable('failed_route', (array)$failure);
		$action_method = AnimeClient::ERROR_MESSAGE_METHOD;

		$params = [];

		if ($failure->failedMethod())
		{
			$params = [
				'http_code' => 405,
				'title' => '405 Method Not Allowed',
				'message' => 'Invalid HTTP Verb'
			];
		}
		else if($failure->failedAccept())
		{
			$params = [
				'http_code' => 406,
				'title' => '406 Not Acceptable',
				'message' => 'Unacceptable content type'
			];
		}
		else
		{
			// Fall back to a 404 message
			$action_method = AnimeClient::NOT_FOUND_METHOD;
		}

		return [
			'params' => $params,
			'action_method' => $action_method
		];
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return array
	 */
	protected function _setup_routes()
	{
		$route_type = $this->get_controller();

		// Add routes
		$routes = [];
		foreach ($this->routes as $name => &$route)
		{
			$path = $route['path'];
			unset($route['path']);

			$controller_map = $this->get_controller_list();
			$controller_class = (array_key_exists($route_type, $controller_map))
				? $controller_map[$route_type]
				: AnimeClient::DEFAULT_CONTROLLER;

			if (array_key_exists($route_type, $controller_map))
			{
				$controller_class = $controller_map[$route_type];
			}

			// Prepend the controller to the route parameters
			$route['controller'] = $controller_class;

			// Select the appropriate router method based on the http verb
			$add = (array_key_exists('verb', $route))
				? "add" . ucfirst(strtolower($route['verb']))
				: "addGet";

			// Add the route to the router object
			if ( ! array_key_exists('tokens', $route))
			{
				$routes[] = $this->router->$add($name, $path)->addValues($route);
			}
			else
			{
				$tokens = $route['tokens'];
				unset($route['tokens']);

				$routes[] = $this->router->$add($name, $path)
					->addValues($route)
					->addTokens($tokens);
			}
		}

		return $routes;
	}
}
// End of Dispatcher.php