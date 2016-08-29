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
use Aviat\Ion\Friend;


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
	 * The route matcher
	 * @var object $matcher
	 */
	protected $matcher;

	/**
	 * Class wrapper for input superglobals
	 * @var Psr\Http\Message\ServerRequestInterface
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
		$this->router = $container->get('aura-router')->getMap();
		$this->matcher = $container->get('aura-router')->getMatcher();
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
		$logger = $this->container->getLogger('default');

		$raw_route = $this->request->getUri()->getPath();
		$route_path = "/" . trim($raw_route, '/');

		$logger->debug('Dispatcher - Routing data from get_route method');
		$logger->debug(print_r([
			'route_path' => $route_path
		], TRUE));

		return $this->matcher->match($this->request);
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
		$logger = $this->container->getLogger('default');

		if (is_null($route))
		{
			$route = $this->get_route();

			$logger->debug('Dispatcher - Route invoke arguments');
			$logger->debug(print_r($route, TRUE));
		}

		if ($route)
		{
			$parsed = $this->process_route(new Friend($route));
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
		if (array_key_exists('controller', $route->attributes))
		{
			$controller_name = $route->attributes['controller'];
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

		$action_method = (array_key_exists('action', $route->attributes))
			? $route->attributes['action']
			: AnimeClient::NOT_FOUND_METHOD;

		$params = [];
		if ( ! empty($route->__get('tokens')))
		{
			$tokens = array_keys($route->__get('tokens'));
			foreach ($tokens as $param)
			{
				if (array_key_exists($param, $route->attributes))
				{
					$params[$param] = $route->attributes[$param];
				}
			}
		}
		$logger = $this->container->getLogger('default');
		$logger->info(json_encode($params));

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
		$request_uri = $this->request->getUri()->getPath();
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
		$path = str_replace('Aviat/AnimeClient/', '', $path);
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
		$logger = $this->container->getLogger('default');

		$controller = new $controller_name($this->container);

		// Run the appropriate controller method
		$logger->debug('Dispatcher - controller arguments');
		$logger->debug(print_r($params, TRUE));
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
		$logger = $this->container->getLogger('default');
		$failure = $this->matcher->getFailedRoute();

		$logger->info('Dispatcher - failed route');
		$logger->info(print_r($failure, TRUE));

		$action_method = AnimeClient::ERROR_MESSAGE_METHOD;

		$params = [];

		switch($failure->failedRule) {
			case 'Aura\Router\Rule\Allows':
				$params = [
					'http_code' => 405,
					'title' => '405 Method Not Allowed',
					'message' => 'Invalid HTTP Verb'
				];
			break;

			case 'Aura\Router\Rule\Accepts':
				$params = [
					'http_code' => 406,
					'title' => '406 Not Acceptable',
					'message' => 'Unacceptable content type'
				];
			break;

			default:
				// Fall back to a 404 message
				$action_method = AnimeClient::NOT_FOUND_METHOD;
			break;
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
				? strtolower($route['verb'])
				: "get";

			// Add the route to the router object
			if ( ! array_key_exists('tokens', $route))
			{
				$routes[] = $this->router->$add($name, $path)->defaults($route);
			}
			else
			{
				$tokens = $route['tokens'];
				unset($route['tokens']);

				$routes[] = $this->router->$add($name, $path)
					->defaults($route)
					->tokens($tokens);
			}
		}

		return $routes;
	}
}
// End of Dispatcher.php