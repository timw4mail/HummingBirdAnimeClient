<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Component;

use Aviat\Ion\Di\ContainerAware;
use function Aviat\AnimeClient\renderTemplate;

/**
 * Shared logic for component-based functionality, like Tabs
 */
trait ComponentTrait
{
	use ContainerAware;

	/**
	 * Render a template with common container values
	 */
	public function render(string $path, array $data): string
	{
		$container = $this->getContainer();
		$helper = $container->get('html-helper');

		$baseData = [
			'auth' => $container->get('auth'),
			'escape' => $helper->escape(),
			'helper' => $helper,
			'url' => $container->get('aura-router')->getGenerator(),
		];

		return renderTemplate(TEMPLATE_DIR . '/' . $path, array_merge($baseData, $data));
	}
}
