<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Component;

final class Tabs {
	use ComponentTrait;

	/**
	 * Creates a tabbed content view
	 *
	 * @param string $name the name attribute for the input[type-option] form elements
	 * also used to generate id attributes
	 * @param array $tabData The data used to create the tab content, indexed by the tab label
	 * @param callable $cb The function to generate the tab content
	 * @param string $className
	 * @param bool $hasSectionWrapper
	 * @return string
	 */
	public function __invoke(
		string $name,
		array $tabData,
		callable $cb,
		string $className = 'content media-wrap flex flex-wrap flex-justify-start',
		bool $hasSectionWrapper = false
	): string
	{
		if (count($tabData) < 2)
		{
			return $this->render('single-tab.php', [
				'name' => $name,
				'data' => $tabData,
				'callback' => $cb,
				'className' => $className . ' single-tab',
				'hasSectionWrapper' => $hasSectionWrapper,
			]);
		}

		return $this->render('tabs.php', [
			'name' => $name,
			'data' => $tabData,
			'callback' => $cb,
			'className' => $className,
			'hasSectionWrapper' => $hasSectionWrapper,
		]);
	}
}