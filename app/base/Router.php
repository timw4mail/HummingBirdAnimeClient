<?php

use Aura\Router\RouterFactory;

class Router {

	public function __construct()
	{
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
		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$route = $this->router->match($path, $_SERVER);

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
		if (is_null($route))
		{
			$route = $this->get_route();
		}

		if ( ! $route)
		{
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

	private function _setup_routes()
	{
		$host = $_SERVER['HTTP_HOST'];
		$controller_class = ($host == "anime.timshomepage.net") ? "AnimeController" : "MangaController";

		$routes = require __DIR__ . '/../config/routes.php';

		// Add the default route
		$this->router->add('home', '/')->addValues([
			'controller' => $controller_class,
			'action' => 'index'
		]);

		// Add routes by the configuration file
		foreach($routes as $name => $route)
		{
			$path = $route['path'];
			unset($route['path']);
			$this->router->add($name, $path)->addValues($route);
		}
	}
}
// End of Router.php