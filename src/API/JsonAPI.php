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
 */

namespace Aviat\AnimeClient\API;

/**
 * Class encapsulating Json API data structure for a request or response
 */
class JsonAPI {

	/**
	 * The full data array
	 *
	 * Basic structure is generally like so:
	 * @example [
	 * 	'id' => '12016665',
	 * 	'type' => 'libraryEntries',
	 * 	'links' => [
	 * 		'self' => 'https://kitsu.io/api/edge/library-entries/13016665'
	 * 	],
	 * 	'attributes' => [
	 *
	 * 	]
	 * ]
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Data array parsed out from a request
	 *
	 * @var array
	 */
	protected $parsedData = [];

	/**
	 * Related objects included with the request
	 *
	 * @var array
	 */
	protected $included = [];

	/**
	 * Pagination links
	 *
	 * @var array
	 */
	protected $links = [];

	/**
	 * JsonAPI constructor
	 *
	 * @param array $initital
	 */
	public function __construct(array $initial = [])
	{
		$this->data = $initial;
	}

	/**
	 * Parse a JsonAPI response into its components
	 *
	 * @param array $data
	 */
	public function parse(array $data)
	{

	}

	/**
	 * Return data array after input is parsed
	 * to inline includes inside of relationship objects
	 *
	 * @return array
	 */
	public function getParsedData(): array
	{

	}
}