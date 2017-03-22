<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

/**
 * Clears the API Cache
 */
class CachePrime extends BaseCommand {
	/**
	 * Clear, then prime the API cache
	 *
	 * @param array $args
	 * @param array $options
	 * @return void
	 * @throws \ConsoleKit\ConsoleException
	 */
	public function execute(array $args, array $options = [])
	{
		$this->setContainer($this->setupContainer());

		$cache = $this->container->get('cache');

		// Save the user id, if it exists, for priming the cache
		$userIdItem = $cache->getItem('kitsu-auth-token');
		$userId = $userIdItem->isHit() ? $userIdItem->get() : null;

		$cache->clear();

		$this->echoBox('Cache cleared, re-priming...');

		if ( ! is_null($userId))
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
