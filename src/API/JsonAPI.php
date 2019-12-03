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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

/**
 * Class encapsulating Json API data structure for a request or response
 */
final class JsonAPI {

	/*
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
	 */

	/**
	 * Inline all included data
	 *
	 * @param array $data - The raw JsonAPI response data
	 * @return array
	 */
	public static function organizeData(array $data): array
	{
		// relationships that have singular data
		$singular = [
			'waifu'
		];

		// Reorganize included data
		$included = array_key_exists('included', $data)
			? static::organizeIncluded($data['included'])
			: [];

		// Inline organized data
		foreach($data['data'] as $i => &$item)
		{
			if ( ! is_array($item))
			{
				continue;
			}

			if (array_key_exists('relationships', $item))
			{
				foreach($item['relationships'] as $relType => $props)
				{

					if (array_keys($props) === ['links'])
					{
						unset($item['relationships'][$relType]);

						if (empty($item['relationships']))
						{
							unset($item['relationships']);
						}

						continue;
					}

					if (array_key_exists('links', $props))
					{
						unset($item['relationships'][$relType]['links']);
					}

					if (array_key_exists('data', $props))
					{
						if (empty($props['data']))
						{
							unset($item['relationships'][$relType]['data']);

							if (empty($item['relationships'][$relType]))
							{
								unset($item['relationships'][$relType]);
							}

							continue;
						}

						// Single data item
						if (array_key_exists('id', $props['data']))
						{
							$idKey = $props['data']['id'];
							$dataType = $props['data']['type'];
							$relationship =& $item['relationships'][$relType];
							unset($relationship['data']);

							if (\in_array($relType, $singular, TRUE))
							{
								$relationship = $included[$dataType][$idKey];
								continue;
							}

							if ($relType === $dataType)
							{
								$relationship[$idKey] = $included[$dataType][$idKey];
								continue;
							}

							$relationship[$dataType][$idKey] = $included[$dataType][$idKey];
						}
						// Multiple data items
						else
						{
							foreach($props['data'] as $j => $datum)
							{
								$idKey = $props['data'][$j]['id'];
								$dataType = $props['data'][$j]['type'];
								$relationship =& $item['relationships'][$relType];

								if ($relType === $dataType)
								{
									$relationship[$idKey] = $included[$dataType][$idKey];
									continue;
								}

								$relationship[$dataType][$idKey][$j] = $included[$dataType][$idKey];
							}

							unset($item['relationships'][$relType]['data']);
						}
					}
				}
			}
		}

		$data['data']['included'] = $included;

		return $data['data'];
	}

	/**
	 * Restructure included data to make it simpler to inline
	 *
	 * @param array $included
	 * @return array
	 */
	public static function organizeIncluded(array $included): array
	{
		$organized = [];

		// First pass, create [ type => items[] ] structure
		foreach($included as &$item)
		{
			$type = $item['type'];
			$id = $item['id'];
			$organized[$type] = $organized[$type] ?? [];
			$newItem = [];

			foreach(['attributes', 'relationships'] as $key)
			{
				if (array_key_exists($key, $item))
				{
					// Remove 'links' type relationships
					if ($key === 'relationships')
					{
						foreach($item['relationships'] as $relType => $props)
						{
							if (array_keys($props) === ['links'])
							{
								unset($item['relationships'][$relType]);
								if (empty($item['relationships']))
								{
									continue 2;
								}
							}
						}
					}

					$newItem[$key] =  $item[$key];
				}
			}

			$organized[$type][$id] = $newItem;
		}

		// Second pass, go through and fill missing relationships in the first pass
		foreach($organized as $type => $items)
		{
			foreach($items as $id => $item)
			{
				if (array_key_exists('relationships', $item) && \is_array($item['relationships']))
				{
					foreach($item['relationships'] as $relType => $props)
					{
						if (array_key_exists('data', $props) && \is_array($props['data']) && array_key_exists('id', $props['data']))
						{
							$idKey = $props['data']['id'];
							$dataType = $props['data']['type'];

							$relationship =& $organized[$type][$id]['relationships'][$relType];
							unset($relationship['links'], $relationship['data']);

							if ($relType === $dataType)
							{
								$relationship[$idKey] = $included[$dataType][$idKey];
								continue;
							}

							if ( ! array_key_exists($dataType, $organized))
							{
								$organized[$dataType] = [];
							}

							if (array_key_exists($idKey, $organized[$dataType]))
							{
								$relationship[$dataType][$idKey] = $organized[$dataType][$idKey];
							}
						}
					}
				}
			}
		}

		return $organized;
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

				if ( ! array_key_exists($type, $included)) continue;

				if (array_key_exists('data', $ids ))
				{
					$ids = array_column($ids['data'], 'id');
				}

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
		$types = array_unique(array_column($includes, 'type'));
		sort($types);

		foreach ($types as $type)
		{
			$organized[$type] = [];
		}

		foreach ($includes as $item)
		{
			$type = $item['type'];
			$id = $item['id'];

			if (array_key_exists('attributes', $item))
			{
				$organized[$type][$id] = $item['attributes'];
			}

			if (array_key_exists('relationships', $item))
			{
				$organized[$type][$id]['relationships'] = static::organizeRelationships($item['relationships']);
			}
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
		$organized = $relationships;

		foreach($relationships as $key => $data)
		{
			$organized[$key] = $organized[$key] ?? [];

			if ( ! array_key_exists('data', $data))
			{
				continue;
			}

			foreach ($data['data'] as $item)
			{
				if (\is_array($item) && array_key_exists('id', $item))
				{
					$organized[$key][] = $item['id'];
				}
			}
		}

		return $organized;
	}
}