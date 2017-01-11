<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Model;

/**
 * Base model for api interaction
 */
class API extends Model {

	use ContainerAware;

	/**
	 * Config manager
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * Cache manager
	 * @var \Aviat\Ion\Cache\CacheInterface
	 */
	protected $cache;

	/**
	 * Default settings for Guzzle
	 * @var array
	 */
	protected $connectionDefaults = [];

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
	}

	/**
	 * Sort the manga entries by their title
	 *
	 * @codeCoverageIgnore
	 * @param array $array
	 * @param string $sort_key
	 * @return void
	 */
	protected function sortByName(array &$array, string $sort_key)
	{
		$sort = [];

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item[$sort_key]['titles'][0];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}
// End of BaseApiModel.php
