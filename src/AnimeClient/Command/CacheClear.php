<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use function Aviat\AnimeClient\clearCache;

/**
 * Clears the API Cache
 */
final class CacheClear extends BaseCommand
{
	/**
	 * Clear the API cache
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());

		$cache = $this->container->get('cache');

		$cleared = clearCache($cache);

		if ($cleared)
		{
			$this->echoBox('API Cache has been cleared.');
		}
		else
		{
			$this->echoErrorBox('Failed to clear cache.');
		}
	}
}
