<?php
/**
 * Routing logic
 */
namespace Aviat\AnimeClient;

use Aura\Web\Request;
use Aura\Web\Response;

use Aviat\Ion\Di\ContainerInterface;

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
	 * Routes added to router
	 * @var array $output_routes
	 */
	protected $output_routes;

	/**
	 * Constructor
	 *
	 * @param Container $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->router = $container->get('aura-router');
		$this->request = $container->get('request');

		$this->output_routes = $this->_setup_routes();
		$this->generate_convention_routes();
	}

	/**
	 * Generate routes based on controller methods
	 *
	 * @return [type] [description]
	 */
	protected function generate_convention_routes()
	{
		$this->output_routes[] = $this->router->add('list', '/{controller}/{type}{/view}')
			->setValues([
				'controller' => $this->routes['convention']['default_controller'],
				'action' => $this->routes['convention']['default_method'],
			])->setTokens([
				'type' => '[a-z_]+',
				'view' => '[a-z_]+'
			]);

		$this->output_routes[] = $this->router->add('generic', '{/controller,action,view}')
			->setValues([
				'controller' => $this->routes['convention']['default_controller'],
				'action' => $this->routes['convention']['default_method'],
				'view' => '',
			]);
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
			$controller_name = $route->params['controller'];
			$action_method = $route->params['action'];

			if (is_null($controller_name))
			{
				throw new \LogicException("Missing controller");
			}

			if (strpos($controller_name, '\\') === FALSE)
			{
				$map = $this->get_controller_list();
				$controller_name = $map[$controller_name];
			}

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
		$convention_routing = $this->routes['convention'];
		$default_namespace = $convention_routing['default_namespace'];
		$path = str_replace('\\', '/', $default_namespace);
		$path = trim($path, '/');
		$actual_path = \_dir(SRC_DIR, $path);

		$class_files = glob("{$actual_path}/*.php");

		$controllers = [];

		foreach($class_files as $file)
		{
			$raw_class_name = basename(str_replace(".php", "", $file));
			$path = strtolower(basename($raw_class_name));
			$class_name = trim($default_namespace . '\\' . $raw_class_name, '\\');

			$controllers[$path] = $class_name;
		}

		return $controllers;
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

			$controller_map = $this->get_controller_list();
			$controller_class = $controller_map[$route_type];

			// Prepend the controller to the route parameters
			$route['controller'] = $controller_class;

			// Select the appropriate router method based on the http verb
			$add = (array_key_exists('verb', $route))
				? "add" . ucfirst(strtolower($route['verb'])) : "addGet";

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