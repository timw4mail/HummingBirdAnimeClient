<?php
/**
 * Base Controller
 */
namespace Aviat\AnimeClient\Base;

/**
 * Base class for controllers, defines output methods
 */
class Controller {

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
	 * @param Container $container
	 * @param array $web
	 */
	public function __construct(Container $container)
	{
		$this->config = $container->get('config');
		$this->base_data['config'] = $this->config;
		$this->base_data['urlGenerator'] = $container->get('url-generator');

		$this->request = $container->get('request');
		$this->response = $container->get('response');

		$this->urlGenerator = $container->get('url-generator');
	}

	/**
	 * Destructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __destruct()
	{
		$this->output();
	}

	/**
	 * Get a class member
	 *
	 * @param string $key
	 * @return object
	 */
	public function __get($key)
	{
		$allowed = ['request', 'response', 'config'];

		if (in_array($key, $allowed))
		{
			return $this->$key;
		}

		return NULL;
	}

	/**
	 * Get the string output of a partial template
	 *
	 * @codeCoverageIgnore
	 * @param string $template
	 * @param array|object $data
	 * @return string
	 */
	public function load_partial($template, $data=[])
	{
		if (isset($this->base_data))
		{
			$data = array_merge($this->base_data, $data);
		}

		global $router, $defaultHandler;
		$route = $router->get_route();
		$data['route_path'] = ($route) ? $router->get_route()->path : "";

		$defaultHandler->addDataTable('Template Data', $data);

		$template_path = _dir(APP_DIR, 'views', "{$template}.php");

		if ( ! is_file($template_path))
		{
			throw new \InvalidArgumentException("Invalid template : {$path}");
		}

		ob_start();
		extract($data);
		include _dir(APP_DIR, 'views', 'header.php');
		include $template_path;
		include _dir(APP_DIR, 'views', 'footer.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @codeCoverageIgnore
	 * @param string $template
	 * @param array|object $data
	 * @return void
	 */
	public function outputHTML($template, $data=[])
	{
		$buffer = $this->load_partial($template, $data);

		$this->response->content->setType('text/html');
		$this->response->content->set($buffer);
	}

	/**
	 * Output json with the proper content type
	 *
	 * @param mixed $data
	 * @return void
	 */
	public function outputJSON($data)
	{
		if ( ! is_string($data))
		{
			$data = json_encode($data);
		}

		$this->response->content->setType('application/json');
		$this->response->content->set($data);
	}

	/**
	 * Redirect to the selected page
	 *
	 * @codeCoverageIgnore
	 * @param string $url
	 * @param int $code
	 * @param string $type
	 * @return void
	 */
	public function redirect($url, $code, $type="anime")
	{
		$url = $this->urlGenerator->full_url($url, $type);

		$this->response->redirect->to($url, $code);
	}

	/**
	 * Add a message box to the page
	 *
	 * @codeCoverageIgnore
	 * @param string $type
	 * @param string $message
	 * @return string
	 */
	public function show_message($type, $message)
	{
		return $this->load_partial('message', [
			'stat_class' => $type,
			'message'  => $message
		]);
	}

	/**
	 * Clear the api session
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	public function logout()
	{
		session_destroy();
		$this->response->redirect->seeOther($this->urlGenerator->full_url(''));
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

		if ($status != "")
		{
			$message = $this->show_message('error', $status);
		}

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message
		]);
	}

	/**
	 * Attempt to log in with the api
	 *
	 * @return void
	 */
	public function login_action()
	{
		if (
			$this->model->authenticate(
				$this->config->hummingbird_username,
				$this->request->post->get('password')
			)
		)
		{
			$this->response->redirect->afterPost($this->urlGenerator->full_url('', $this->base_data['url_type']));
			return;
		}

		$this->login("Invalid username or password.");
	}

	/**
	 * Send the appropriate response
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	private function output()
	{
		// send status
		@header($this->response->status->get(), true, $this->response->status->getCode());

		// headers
		foreach($this->response->headers->get() as $label => $value)
		{
			@header("{$label}: {$value}");
		}

		// cookies
		foreach($this->response->cookies->get() as $name => $cookie)
		{
			@setcookie(
				$name,
				$cookie['value'],
				$cookie['expire'],
				$cookie['path'],
				$cookie['domain'],
				$cookie['secure'],
				$cookie['httponly']
			);
		}

		// send the actual response
		echo $this->response->content->get();
	}
}
// End of BaseController.php
