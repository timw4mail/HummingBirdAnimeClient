<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient;

class AnimeClient {

	use \Aviat\Ion\Di\ContainerAware;
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
	 * Determine whether to show the sub-menu
	 *
	 * @return bool
	 */
	public function is_view_page()
	{
		$url = $this->container->get('request')
			->server->get('REQUEST_URI');
		$blacklist = ['edit', 'add', 'update', 'login', 'logout'];
		$page_segments = explode("/", $url);

		$intersect = array_intersect($page_segments, $blacklist);

		return empty($intersect);
	}

}
// End of anime_client.php