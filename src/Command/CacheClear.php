<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

/**
 * Clears the API Cache
 */
final class CacheClear extends BaseCommand {
	/**
	 * Clear the API cache
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
		$cache->clear();

		$this->echoBox('API Cache has been cleared.');
	}
}
