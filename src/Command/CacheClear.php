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

use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Clears the API Cache
 */
final class CacheClear extends BaseCommand {
	/**
	 * Clear the API cache
	 *
	 * @param array $args
	 * @param array $options
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return void
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());

		$this->container->get('cache')->clear();

		$this->echoBox('API Cache has been cleared.');
	}
}
