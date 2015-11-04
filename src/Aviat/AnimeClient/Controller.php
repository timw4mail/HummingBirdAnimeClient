<?php
/**
 * Base Controller
 */
namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\View\HttpView;
use Aviat\Ion\View\HtmlView;
use Aviat\Ion\View\JsonView;

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
		'menu_name' => ''
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
		$this->base_data['auth'] = $container->get('auth');
		$this->base_data['config'] = $this->config;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * Redirect to the default controller/url from an empty path
	 */
	public function redirect_to_default()
	{
		$default_type = $this->config->get(['routing', 'default_list']);
		$this->redirect($this->urlGenerator->default_url($default_type), 303);
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
	protected function load_partial($view, $template, array $data = [])
	{
		$errorHandler = $this->container->get('error-handler');
		$errorHandler->addDataTable('Template Data', $data);
		$router = $this->container->get('dispatcher');

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
	 * @param array $data
	 * @return void
	 */
	protected function render_full_page($view, $template, array $data)
	{
		$view->appendOutput($this->load_partial($view, 'header', $data));
		$view->appendOutput($this->load_partial($view, $template, $data));
		$view->appendOutput($this->load_partial($view, 'footer', $data));
	}

	/**
	 * Show the login form
	 *
	 * @codeCoverageIgnore
	 * @param string $status
	 * @return void
	 */
	public function login($status="")
	{
		$message = "";

		$view = new HtmlView($this->container);

		if ($status != "")
		{
			$message = $this->show_message($view, 'error', $status);
		}

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message
		], $view);
	}

	/**
	 * Add a message box to the page
	 *
	 * @codeCoverageIgnore
	 * @param HtmlView $view
	 * @param string $type
	 * @param string $message
	 * @return string
	 */
	protected function show_message($view, $type, $message)
	{
		return $this->load_partial($view, 'message', [
			'stat_class' => $type,
			'message'  => $message
		]);
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @param string $template
	 * @param array $data
	 * @param HtmlView|null $view
	 * @return void
	 */
	protected function outputHTML($template, array $data = [], $view = NULL)
	{
		if (is_null($view))
		{
			$view = new HtmlView($this->container);
		}

		$this->render_full_page($view, $template, $data);
	}

	/**
	 * Output a JSON Response
	 *
	 * @param mixed $data
	 * @return void
	 */
	protected function outputJSON($data = [])
	{
		$view = new JsonView($this->container);
		$view->setOutput($data);
	}

	/**
	 * Redirect to the selected page
	 *
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	protected function redirect($url, $code)
	{
		$http = new HttpView($this->container);
		$http->redirect($url, $code);
	}
}
// End of BaseController.php