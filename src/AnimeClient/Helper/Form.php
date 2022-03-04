<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Helper;

use Aviat\AnimeClient\FormGenerator;
use Aviat\Ion\Di\ContainerAware;

/**
 * FormGenerator helper wrapper
 */
final class Form
{
	use ContainerAware;

	/**
	 * Create the html for the specified form
	 *
	 * @return string
	 */
	public function __invoke(string $name, array $form)
	{
		return FormGenerator::new($this->container)->generate($name, $form);
	}
}
