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
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

/**
 * Clears the API Cache
 */
final class CachePrime extends BaseCommand {
	/**
	 * Clear, then prime the API cache
	 *
	 * @param array $args
	 * @param array $options
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return void
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());

		$cache = $this->container->get('cache');

		// Save the user id, if it exists, for priming the cache
		$userIdItem = $cache->getItem('kitsu-auth-token');
		$userId = $userIdItem->isHit() ? $userIdItem->get() : null;

		$cache->clear();

		$this->echoBox('Cache cleared, re-priming...');

		if ($userId !== NULL)
		{
			$userIdItem = $cache->getItem('kitsu-auth-token');
			$userIdItem->set($userId);
			$userIdItem->save();
		}

		// Prime anime list cache
		$kitsuModel = $this->container->get('kitsu-model');
		$kitsuModel->getFullOrganizedAnimeList();

		// Prime manga list cache
		$kitsuModel->getFullOrganizedMangaList();

		$this->echoBox('API Cache has been primed.');
	}
}
