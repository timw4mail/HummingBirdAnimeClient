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

namespace Aviat\AnimeClient\API;

use Aviat\Ion\Json;

/**
 * Class encapsulating Json API data structure for a request or response
 */
class JsonAPI {

	/**
	 * The full data array
	 *
	 * Basic structure is generally like so:
	 * [
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
	
	public static function inlineRawIncludes(array &$data, string $key): array
	{
		foreach($data['data'] as $i => &$item)
		{
			$item[$key] = $data['included'][$i];
		}
		
		return $data['data'];
	}
	
	/**
	 * Take organized includes and inline them, where applicable
	 *
	 * @param array $included
	 * @param string $key The key of the include to inline the other included values into
	 * @return array
	 */
	public static function inlineIncludedRelationships(array $included, string $key): array
	{
		$inlined = [
			$key => []
		];
		
		foreach ($included[$key] as $itemId => $item)
		{
			// Duplicate the item for the output
			$inlined[$key][$itemId] = $item;
			
			foreach($item['relationships'] as $type => $ids)
			{
				$inlined[$key][$itemId]['relationships'][$type] = [];
				foreach($ids as $id)
				{
					$inlined[$key][$itemId]['relationships'][$type][$id] = $included[$type][$id];
				}
			}
		}
		
		return $inlined;
	}

	/**
	 * Reorganizes 'included' data to be keyed by
	 * 	type => [
	 * 		id => data/attributes,
	 * 	]
	 *
	 * @param array $includes
	 * @return array
	 */
	public static function organizeIncludes(array $includes): array
	{
		$organized = [];

		foreach ($includes as $item)
		{
			$type = $item['type'];
			$id = $item['id'];
			$organized[$type] = $organized[$type] ?? [];
			$organized[$type][$id] = $item['attributes'];

			if (array_key_exists('relationships', $item))
			{
				$organized[$type][$id]['relationships'] = static::organizeRelationships($item['relationships']);
			}
		}

		return $organized;
	}
	
	public static function lightlyOrganizeIncludes(array $includes): array
	{
		$organized = [];
		
		foreach($includes as $item)
		{
			$type = $item['type'];
			$id = $item['id'];
			$organized[$type] = $organized[$type] ?? [];
			$newItem = [];
			
			foreach(['attributes', 'relationships'] as $key)
			{
				if (array_key_exists($key, $item))
				{
					$newItem[$key] =  $item[$key];
				}
			}
			
			$organized[$type][$id] = $newItem;
		}
		
		return $organized;
	}

	/**
	 * Reorganize relationship mappings to make them simpler to use
	 *
	 * Remove verbose structure, and just map:
	 * 	type => [ idArray ]
	 *
	 * @param array $relationships
	 * @return array
	 */
	public static function organizeRelationships(array $relationships): array
	{
		$organized = [];

		foreach($relationships as $key => $data)
		{
			if ( ! array_key_exists('data', $data))
			{
				continue;
			}

			$organized[$key] = $organized[$key] ?? [];

			foreach ($data['data'] as $item)
			{
				$organized[$key][] = $item['id'];
			}
		}

		return $organized;
	}
}