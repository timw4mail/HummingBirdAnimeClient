<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Router\Map;
use Aura\Router\Matcher;
use Aura\Router\Route;
use Aura\Router\Rule;
use Aviat\AnimeClient\API\FailedResponseException;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Friend;
use Aviat\Ion\Json;
use Aviat\Ion\Type\StringType;
use LogicException;
use ReflectionException;

use function Aviat\Ion\_dir;

/**
 * Basic routing/ dispatch
 */
final class Dispatcher extends RoutingBase
{
	/**
	 * The route-matching object
	 */
	protected Map $router;

	/**
	 * The route matcher
	 */
	protected Matcher $matcher;

	/**
	 * Routing array
	 * @var array<string, mixed>
	 */
	protected array $routes = [];

	/**
	 * Routes added to router
	 * @var list<mixed>
	 */
	protected array $outputRoutes = [];

	/**
	 * Constructor
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
	 * Handle the current route
	 *
	 * @throws ReflectionException
	 */
	public function __invoke(null|object $route = null): void
	{
		$logger = $this->container->getLogger();

		if ($route === null)
		{
			$route = $this->getRoute();

			$logger?->info('Dispatcher - Route invoke arguments');
			$logger?->info(print_r($route, true));
		}

		if (! $route)
		{
			// If not route was matched, return an appropriate http
			// error message
			$errorRoute = $this->getErrorParams();
			$controllerName = DEFAULT_CONTROLLER;
			$actionMethod = $errorRoute['action_method'];
			$params = $errorRoute['params'];
			$this->call($controllerName, $actionMethod, $params);

			return;
		}

		$parsed = $this->processRoute(new Friend($route));
		$controllerName = $parsed['controller_name'];
		$actionMethod = $parsed['action_method'];
		$params = $parsed['params'];

		$this->call($controllerName, $actionMethod, $params);
	}

	/**
	 * Get the current route object, if one matches
	 */
	public function getRoute(): Route|false
	{
		$logger = $this->container->getLogger();

		$rawRoute = $this->request->getUri()->getPath();
		$routePath = '/' . trim($rawRoute, '/');

		if ($logger !== null)
		{
			$logger->info('Dispatcher - Routing data from get_route method');
			$logger->info(print_r([
				'route_path' => $routePath,
			], true));
		}

		return $this->matcher->match($this->request);
	}

	/**
	 * Get list of routes applied
	 *
	 * @return mixed[]
	 */
	public function getOutputRoutes(): array
	{
		return $this->outputRoutes;
	}

	/**
	 * Parse out the arguments for the appropriate controller for
	 * the current route
	 *
	 * @param Friend<Route> $route
	 * @throws LogicException
	 * @return array<string, mixed>
	 */
	protected function processRoute(Friend $route): array
	{
		if (! array_key_exists('controller', $route->attributes))
		{
			throw new LogicException('Missing controller');
		}

		$controllerName = $route->attributes['controller'];

		// Get the full namespace for a controller if a short name is given
		if (! str_contains($controllerName, '\\'))
		{
			$map = $this->getControllerList();
			$controllerName = $map[$controllerName];
		}

		$actionMethod = array_key_exists('action', $route->attributes)
			? $route->attributes['action']
			: NOT_FOUND_METHOD;

		$params = [];
		if (! empty($route->__get('tokens')))
		{
			$tokens = array_keys($route->__get('tokens'));

			foreach ($tokens as $param)
			{
				if (! array_key_exists($param, $route->attributes))
				{
					continue;
				}

				$params[$param] = $route->attributes[$param];
			}
		}

		$logger = $this->container->getLogger();
		$logger?->info(Json::encode($params));

		return [
			'controller_name' => $controllerName,
			'action_method' => $actionMethod,
			'params' => $params,
		];
	}

	/**
	 * Get the type of route, to select the current controller
	 */
	public function getController(): string
	{
		$routeType = $this->config->get('default_list');
		$requestUri = $this->request->getUri()->getPath();
		$path = trim($requestUri, '/');

		$segments = explode('/', $path);
		$controller = reset($segments);

		$logger = $this->container->getLogger();
		$logger?->info('Controller: ' . $controller);

		if (empty($controller))
		{
			$controller = $routeType;
		}

		return $controller ?? '';
	}

	/**
	 * Get the list of controllers in the default namespace
	 * @return array<string, string>
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
		if ($classFiles === false)
		{
			return [];
		}

		$controllers = [];

		foreach ($classFiles as $file)
		{
			$rawClassName = basename(str_replace('.php', '', $file));
			$path = (string) StringType::from($rawClassName)->dasherize();
			$className = trim($defaultNamespace . '\\' . $rawClassName, '\\');

			$controllers[$path] = $className;
		}

		return $controllers;
	}

	/**
	 * Create the controller object and call the appropriate
	 * method
	 *
	 * @param string $controllerName - The full namespace of the controller class
	 * @param array<mixed> $params
	 */
	protected function call(string $controllerName, string $method, array $params): void
	{
		$logger = $this->container->getLogger();

		try {
			$controller = new $controllerName($this->container);

			// Run the appropriate controller method
			$logger?->debug('Dispatcher - controller arguments', $params);

			$params = array_values($params);
			$controller->{$method}(...$params);
		}
		catch (FailedResponseException) {
			$controllerName = DEFAULT_CONTROLLER;
			$controller = new $controllerName($this->container);
			$controller->errorPage(
				500,
				'API request timed out',
				'Failed to retrieve data from API (╯°□°)╯︵ ┻━┻',
			);
		}
	}

	/**
	 * Get the appropriate params for the error page
	 * passed on the failed route
	 *
	 * @return array<string, mixed>
	 */
	protected function getErrorParams(): array
	{
		$logger = $this->container->getLogger();
		$failure = $this->matcher->getFailedRoute();

		if ($logger !== null)
		{
			$logger->info('Dispatcher - failed route');
			$logger->info(print_r($failure, true));
		}

		$actionMethod = ERROR_MESSAGE_METHOD;

		$params = [];

		switch ($failure?->failedRule)
		{
			case Rule\Allows::class:
				$params = [
					'http_code' => 405,
					'title' => '405 Method Not Allowed',
					'message' => 'Invalid HTTP Verb',
				];
				break;

			case Rule\Accepts::class:
				$params = [
					'http_code' => 406,
					'title' => '406 Not Acceptable',
					'message' => 'Unacceptable content type',
				];
				break;

			default:
				// Fall back to a 404 message
				$actionMethod = NOT_FOUND_METHOD;
				break;
		}

		return [
			'params' => $params,
			'action_method' => $actionMethod,
		];
	}

	/**
	 * Select controller based on the current url, and apply its relevant routes
	 * @return list<mixed>
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
			$verb = array_key_exists('verb', $route)
				? strtolower($route['verb'])
				: 'get';

			// Add the route to the router object
			if (! array_key_exists('tokens', $route))
			{
				$routes[] = $this->router->{$verb}($name, $path)->defaults($route);

				continue;
			}

			$tokens = $route['tokens'];
			unset($route['tokens']);

			$routes[] = $this->router
				->{$verb}($name, $path)
				->defaults($route)
				->tokens($tokens);
		}

		return $routes;
	}
}

// End of Dispatcher.php
