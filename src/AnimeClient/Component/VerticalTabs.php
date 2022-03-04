<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Component;

final class VerticalTabs
{
	use ComponentTrait;

	/**
	 * Creates a vertical tab content view
	 *
	 * @param string $name the name attribute for the input[type-option] form elements
	 *                     also used to generate id attributes
	 * @param array $tabData The data used to create the tab content, indexed by the tab label
	 * @param callable $cb The function to generate the tab content
	 */
	public function __invoke(
		string $name,
		array $tabData,
		callable $cb,
		string $className = 'content media-wrap flex flex-wrap flex-justify-start'
	): string {
		return $this->render('vertical-tabs.php', [
			'name' => $name,
			'data' => $tabData,
			'callback' => $cb,
			'className' => $className,
		]);
	}
}
