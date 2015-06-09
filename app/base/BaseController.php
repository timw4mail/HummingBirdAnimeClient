<?php

class BaseController {

	protected $config;

	public function __construct()
	{
		global $config;
		$this->config = $config;
	}

	/**
	 * Output a template to HTML, using the provided data
	 *
	 * @param string $template
	 * @param array/object $data
	 * @return void
	 */
	public function outputHTML($template, $data=[])
	{
		global $router;
		$route = $router->get_route();
		$data['route_path'] = ($route) ? $router->get_route()->path : "";

		$path = realpath(__DIR__ . "/../views/{$template}.php");

		if ( ! is_file($path))
		{
			throw new Exception("Invalid template : {$path}");
		}

		ob_start();
		extract($data);
		include $path;
		$buffer = ob_get_contents();
		ob_end_clean();

		header("Content-type: text/html;charset=utf-8");
		echo $buffer;
		die();
	}

	/**
	 * Output json with the proper content type
	 *
	 * @param mixed data
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
}