<?php
/**
 * Base Controller
 */

/**
 * Base class for controllers, defines output methods
 */
class BaseController {

	/**
	 * The global configuration object
	 * @var object $config
	 */
	protected $config;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $config;
		$this->config = $config;
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @param string $template
	 * @param array|object $data
	 * @return void
	 */
	public function outputHTML($template, $data=[])
	{
		global $router, $defaultHandler;
		$route = $router->get_route();
		$data['route_path'] = ($route) ? $router->get_route()->path : "";

		$defaultHandler->addDataTable('Template Data', $data);

		$template_path = _dir(APP_DIR, 'views', "{$template}.php");

		if ( ! is_file($template_path))
		{
			throw new Exception("Invalid template : {$path}");
			die();
		}

		ob_start();
		extract($data);
		include _dir(APP_DIR, 'views', 'header.php');
		include $template_path;
		include _dir(APP_DIR, 'views', 'footer.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		header("Content-type: text/html;charset=utf-8");
		echo $buffer;
		die();
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

		header("Content-type: application/json");
		echo $data;
	}

	/**
	 * Redirect to the selected page
	 *
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	public function redirect($url, $code, $type="anime")
	{
		$url = full_url($url, $type);

		$codes = [
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other'
		];

		header("HTTP/1.1 {$code} {$codes[$code]}");
		header("Location: {$url}");
		die();
	}

	/**
	 * Clear the api session
	 *
	 * @return void
	 */
	public function logout()
	{
		session_destroy();
		$this->redirect('');
	}
}
// End of BaseController.php