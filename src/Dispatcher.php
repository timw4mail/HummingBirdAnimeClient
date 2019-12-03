<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use function Aviat\Ion\_dir;

use Aura\Router\{Matcher, Rule};

use Aviat\AnimeClient\API\FailedResponseException;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Friend;
use Aviat\Ion\StringWrapper;

/**
 * Basic routing/ dispatch
 */
final class Dispatcher extends RoutingBase {

	use StringWrapper;

	/**
	 * The route-matching object
	 * @var object $router
	 */
	protected $router;

	/**
	 * The route matcher
	 * @var Matcher $matcher
	 */
	protected $matcher;

	/**
	 * Routing array
	 * @var array
	 */
	protected $routes;

	/**
	 * Routes added to router
	 * @var array $outputRoutes
	 */
	protected $outputRoutes;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$router = $this->container->get('aura-router');
		$this->router = $router->getMap();
		$this->matcher = $router->getMatcher();
		$this->routes = $this->config->get('routes');
		$this->outputRoutes = $this->setupRoutes();
	}

	/**
	 * Get the current route object, if one matches
	 *
	 * @return \Aura\Router\Route|false
	 */
	public function getRoute()
	{
		$logger = $this->container->getLogger();

		$rawRoute = $this->request->getUri()->getPath();
		$routePath = '/' . trim($rawRoute, '/');

		if ($logger !== NULL)
		{
			$logger->info('Dispatcher - Routing data from get_route method');
			$logger->info(print_r([
				'route_path' => $routePath
			], TRUE));
		}

		return $this->matcher->match($this->request);
	}

	/**
	 * Get list of routes applied
	 *
	 * @return array
	 */
	public function getOutputRoutes(): array
	{
		return $this->outputRoutes;
	}

	/**
	 * Handle the current route
	 *
	 * @param object|null $route
	 * @return void
	 */
	public function __invoke($route = NULL): void
	{
		$logger = $this->container->getLogger();

		if ($route === NULL)
		{
			$route = $this->getRoute();

			if ($logger !== NULL)
			{
				$logger->info('Dispatcher - Route invoke arguments');
				$logger->info(print_r($route, TRUE));
			}
		}

		if ($route)
		{
			$parsed = $this->processRoute(new Friend($route));
			$controllerName = $parsed['controller_name'];
			$actionMethod = $parsed['action_method'];
			$params = $parsed['params'];
		}
		else
		{
			// If not route was matched, return an appropriate http
			// error message
			$errorRoute = $this->getErrorParams();
			$controllerName = DEFAULT_CONTROLLER;
			$actionMethod = $errorRoute['action_method'];
			$params = $errorRoute['params'];
		}

		$this->call($controllerName, $actionMethod, $params);
	}

	/**
	 * Parse out the arguments for the appropriate controller for
	 * the current route
	 *
	 * @param \Aura\Router\Route $route
	 * @throws \LogicException
	 * @return array
	 */
	protected function processRoute($route): array
	{
		if (array_key_exists('controller', $route->attributes))
		{
			$controllerName = $route->attributes['controller'];
		}
		else
		{
			throw new \LogicException('Missing controller');
		}

		// Get the full namespace for a controller if a short name is given
		if (strpos($controllerName, '\\') === FALSE)
		{
			$map = $this->getControllerList();
			$controllerName = $map[$controllerName];
		}

		$actionMethod = array_key_exists('action', $route->attributes)
			? $route->attributes['action']
			: NOT_FOUND_METHOD;

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
		$logger = $this->container->getLogger();
		if ($logger !== NULL)
		{
			$logger->info(json_encode($params));
		}

		return [
			'controller_name' => $controllerName,
			'action_method' => $actionMethod,
			'params' => $params
		];
	}

	/**
	 * Get the type of route, to select the current controller
	 *
	 * @return string
	 */
	public function getController(): string
	{
		$routeType = $this->config->get('default_list');
		$requestUri = $this->request->getUri()->getPath();
		$path = trim($requestUri, '/');

		$segments = explode('/', $path);
		$controller = reset($segments);

		$logger = $this->container->getLogger();
		if ($logger !== NULL)
		{
			$logger->info('Controller: ' . $controller);
		}

		if (empty($controller))
		{
			$controller = $routeType;
		}

		return $controller ?? '';
	}

	/**
	 * Get the list of controllers in the default namespace
	 *
	 * @return array
	 */
	public function getControllerList(): array
	{
		$defaultNamespace = DEFAULT_CONTROLLER_NAMESPACE;
		$find = ['\\', 'Aviat/AnimeClient/'];
		$replace = ['/', ''];

		$path = str_replace($find, $replace, $defaultNamespace);
		$path = trim($path, '/');
		$actualPath = realpath(_dir(SRC_DIR, $path));
		$classFiles = glob("{$actualPath}/*.php");

		$controllers = [];

		foreach ($classFiles as $file)
		{
			$rawClassName = basename(str_replace('.php', '', $file));
			$path = $this->string($rawClassName)->dasherize()->__toString();
			$className = trim($defaultNamespace . '\\' . $rawClassName, '\\');

			$controllers[$path] = $className;
		}

		return $controllers;
	}

	/**
	 * Create the controller object and call the appropriate
	 * method
	 *
	 * @param  string $controllerName - The full namespace of the controller class
	 * @param  string $method
	 * @param  array  $params
	 * @return void
	 */
	protected function call($controllerName, $method, array $params): void
	{
		$logger = $this->container->getLogger('default');

		try
		{
			$controller = new $controllerName($this->container);

			// Run the appropriate controller method
			if ($logger !== NULL)
			{
				$logger->debug('Dispatcher - controller arguments', $params);
			}

			\call_user_func_array([$controller, $method], $params);
		}
		catch (FailedResponseException $e)
		{
			$controllerName = DEFAULT_CONTROLLER;
			$controller = new $controllerName($this->container);
			$controller->errorPage(500,
				'API request timed out',
				'Failed to retrieve data from API (╯°□°)╯︵ ┻━┻');
		}

	}

	/**
	 * Get the appropriate params for the error page
	 * pased on the failed route
	 *
	 * @return array|false
	 */
	protected function getErrorParams()
	{
		$logger = $this->container->getLogger();
		$failure = $this->matcher->getFailedRoute();

		if ($logger !== NULL)
		{
			$logger->info('Dispatcher - failed route');
			$logger->info(print_r($failure, TRUE));
		}

		$actionMethod = ERROR_MESSAGE_METHOD;

		$params = [];

		switch($failure->failedRule) {
			case Rule\Allows::class:
				$params = [
					'http_code' => 405,
					'title' => '405 Method Not Allowed',
					'message' => 'Invalid HTTP Verb'
				];
			break;

			case Rule\Accepts::class:
				$params = [
					'http_code' => 406,
					'title' => '406 Not Acceptable',
					'message' => 'Unacceptable content type'
				];
			break;

			default:
				// Fall back to a 404 message
				$actionMethod = NOT_FOUND_METHOD;
			break;
		}

		return [
			'params' => $params,
			'action_method' => $actionMethod
		];
	}

	/**
	 * Select controller based on the current url, and apply its relevent routes
	 *
	 * @return array
	 */
	protected function setupRoutes(): array
	{
		$routeType = $this->getController();

		// Add routes
		$routes = [];
		foreach ($this->routes as $name => &$route)
		{
			$path = $route['path'];
			unset($route['path']);

			$controllerMap = $this->getControllerList();
			$controllerClass = array_key_exists($routeType, $controllerMap)
				? $controllerMap[$routeType]
				: DEFAULT_CONTROLLER;

			// If there's an explicit controller, try to find
			// the full namespaced class name
			if (array_key_exists('controller', $route))
			{
				$controllerKey = $route['controller'];
				if (array_key_exists($controllerKey, $controllerMap))
				{
					$controllerClass = $controllerMap[$controllerKey];
				}
			}

			// Prepend the controller to the route parameters
			$route['controller'] = $controllerClass;

			// Select the appropriate router method based on the http verb
			$add = array_key_exists('verb', $route)
				? strtolower($route['verb'])
				: 'get';

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