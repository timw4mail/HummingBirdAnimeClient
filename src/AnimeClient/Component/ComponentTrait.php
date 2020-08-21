<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Component;

/**
 * Shared logic for component-based functionality, like Tabs
 */
trait ComponentTrait {
	public function render(string $path, array $data): string
	{
		ob_start();
		extract($data, EXTR_OVERWRITE);
		include \TEMPLATE_DIR . '/' .$path;
		return ob_get_clean();
	}
}