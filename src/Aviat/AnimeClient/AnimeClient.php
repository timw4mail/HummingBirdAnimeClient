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

/**
 * Odds and Ends class
 */
class AnimeClient {

	use \Aviat\Ion\Di\ContainerAware;

	const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
	const DEFAULT_CONTROLLER_NAMESPACE = 'Aviat\AnimeClient\Controller';
	const DEFAULT_CONTROLLER = 'Aviat\AnimeClient\Controller\Anime';
	const DEFAULT_CONTROLLER_METHOD = 'index';
	const NOT_FOUND_METHOD = 'not_found';
	const ERROR_MESSAGE_METHOD = 'error_page';
	const SRC_DIR = __DIR__ . '/../../';

	private static $form_pages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout'
	];

	/**
	 * HTML selection helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function is_selected($a, $b)
	{
		return ($a === $b) ? 'selected' : '';
	}

	/**
	 * Inverse of selected helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function is_not_selected($a, $b)
	{
		return ($a !== $b) ? 'selected' : '';
	}

	/**
	 * Decode a json file into a php data structure
	 *
	 * @param  string $file
	 * @param  bool $as_array
	 * @return array|object
	 */
	public static function json_file_decode($file, $as_array=TRUE)
	{
	    return json_decode(
	        file_get_contents($file),
	        $as_array
	    );
	}

	/**
	 * Encode json data and save to the selected file
	 *
	 * @param string $file
	 * @param mixed $data
	 * @return bool
	 */
	public static function json_file_encode($file, $data)
	{
		return file_put_contents(
			$file,
			json_encode($data)
		);
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @return bool
	 */
	public function is_view_page()
	{
		$url = $this->container->get('request')
			->url->get();
		$page_segments = explode("/", $url);

		$intersect = array_intersect($page_segments, self::$form_pages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @return boolean
	 */
	public function is_form_page()
	{
		return ! $this->is_view_page();
	}

}
// End of anime_client.php