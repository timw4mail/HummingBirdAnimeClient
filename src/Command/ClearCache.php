<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Command;

/**
 * Clears the API Cache
 */
class ClearCache extends BaseCommand {
	/**
	 * Run the image conversion script
	 *
	 * @param array $args
	 * @param array $options
	 * @return void
	 * @throws \ConsoleKit\ConsoleException
	 */
	public function execute(array $args, array $options = array())
	{
		$this->setContainer($this->setupContainer());
		$cache = $this->container->get('cache');
		$cache->purge();
		
		$this->echoBox('API Cache has been cleared.');
	}
}
// End of ClearCache.php