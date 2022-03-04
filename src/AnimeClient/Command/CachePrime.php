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

namespace Aviat\AnimeClient\Command;

use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use function Aviat\AnimeClient\clearCache;

/**
 * Clears the API Cache
 */
final class CachePrime extends BaseCommand
{
	/**
	 * Clear, then prime the API cache
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$cache = $this->container->get('cache');

		$cleared = clearCache($cache);
		if ( ! $cleared)
		{
			$this->echoErrorBox('Failed to clear cache.');

			return;
		}

		$this->echoBox('Cache cleared, re-priming...');

		$kitsuModel = $this->container->get('kitsu-model');

		// Prime anime list and history cache
		$kitsuModel->getAnimeHistory();
		$kitsuModel->getFullOrganizedAnimeList();

		// Prime manga list cache
		$kitsuModel->getMangaHistory();
		$kitsuModel->getFullOrganizedMangaList();

		$this->echoBox('API Cache has been primed.');
	}
}
