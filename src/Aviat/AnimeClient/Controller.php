<?php
/**
 * Base Controller
 */
namespace Aviat\AnimeClient;

use \Aviat\Ion\Di\ContainerInterface;
use \Aviat\Ion\View\HttpView;
use \Aviat\Ion\View\HtmlView;
use \Aviat\Ion\View\JsonView;

/**
 * Controller base, defines output methods
 *
 * @property Response object $response
 * @property Config object $config
 */
class Controller {

	use \Aviat\Ion\Di\ContainerAware;

	/**
	 * The global configuration object
	 * @var object $config
	 */
	protected $config;

	/**
	 * Request object
	 * @var object $request
	 */
	protected $request;

	/**
	 * Response object
	 * @var object $response
	 */
	protected $response;

	/**
	 * The api model for the current controller
	 * @var object
	 */
	protected $model;

	/**
	 * Url generatation class
	 * @var UrlGenerator
	 */
	protected $urlGenerator;

	/**
	 * Common data to be sent to views
	 * @var array
	 */
	protected $base_data = [
		'url_type' => 'anime',
		'other_type' => 'manga',
		'nav_routes' => []
	];

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$urlGenerator = $container->get('url-generator');
		$this->config = $container->get('config');
		$this->request = $container->get('request');
		$this->response = $container->get('response');
		$this->base_data['urlGenerator'] = $urlGenerator;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Get a class member
	 *
	 * @param string $key
	 * @return object
	 */
	public function __get($key)
	{
		$allowed = ['response', 'config'];

		if (in_array($key, $allowed))
		{
			return $this->$key;
		}

		return NULL;
	}

	/**
	 * Get the string output of a partial template
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array $data
	 * @return string
	 */
	public function load_partial($view, $template, array $data = [])
	{
		$errorHandler = $this->container->get('error-handler');
		$errorHandler->addDataTable('Template Data', $data);
		$router = $this->container->get('router');

		if (isset($this->base_data))
		{
			$data = array_merge($this->base_data, $data);
		}

		$route = $router->get_route();
		$data['route_path'] = ($route) ? $router->get_route()->path : "";


		$template_path = _dir($this->config->get('view_path'), "{$template}.php");

		if ( ! is_file($template_path))
		{
			throw new \InvalidArgumentException("Invalid template : {$template}");
		}

		return $view->render_template($template_path, (array)$data);
	}

	/**
	 * Render a template with header and footer
	 *
	 * @param HtmlView $view
	 * @param string $template
	 * @param array|object $data
	 * @return void
	 */
	public function render_full_page($view, $template, $data)
	{
		$view->appendOutput($this->load_partial($view, 'header', $data));
		$view->appendOutput($this->load_partial($view, $template, $data));
		$view->appendOutput($this->load_partial($view, 'footer', $data));
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @param string $template
	 * @param array|object $data
	 * @return void
	 */
	public function outputHTML($template, $data = [])
	{
		$view = new HtmlView($this->container);
		$this->render_full_page($view, $template, $data);
	}

	/**
	 * Output a JSON Response
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function outputJSON($data = [])
	{
		$view = new JsonView($this->container);
		$view->setOutput($data);
	}

	/**
	 * Redirect to the selected page
	 *
	 * @param string $path
	 * @param int $code
	 * @param string $type
	 * @return void
	 */
	public function redirect($path, $code, $type = "anime")
	{
		$url = $this->urlGenerator->full_url($path, $type);
		$http = new HttpView($this->container);

		$http->redirect($url, $code);
	}

	/**
	 * Clear the api session
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->response->redirect->seeOther($this->urlGenerator->full_url(''));
	}

	/**
	 * Show the login form
	 *
	 * @param string $status
	 * @return void
	 */
	public function login($status = "")
	{
		$message = "";

		/*if ($status != "")
		{
			$message = $this->show_message('error', $status);
		}

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message
		]);*/
	}

	/**
	 * Attempt to log in with the api
	 *
	 * @return void
	 */
	public function login_action()
	{
		$request = $this->container->get('request');

		if (
			$this->model->authenticate(
				$this->config->hummingbird_username,
				$request->post->get('password')
			)
		)
		{
			$this->response->redirect->afterPost(
				$this->urlGenerator->full_url('', $this->base_data['url_type'])
			);
			return;
		}

		$this->login("Invalid username or password.");
	}
}
// End of BaseController.php