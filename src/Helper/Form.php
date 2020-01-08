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
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Helper;

use Aviat\AnimeClient\FormGenerator;
use Aviat\Ion\Di\ContainerAware;

/**
 * FormGenerator helper wrapper
 */
final class Form {

	use ContainerAware;

	/**
	 * Create the html for the specified form
	 *
	 * @param string $name
	 * @param array $form
	 * @return string
	 */
	public function __invoke(string $name, array $form)
	{
		return (new FormGenerator($this->container))->generate($name, $form);
	}
}
