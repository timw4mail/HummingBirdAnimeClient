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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\Types\Person;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for people pages
 */
final class PersonTransformer extends AbstractTransformer {

	public function transform($personData): Person
	{
		$data = JsonAPI::organizeData($personData);
		$included = JsonAPI::organizeIncludes($personData['included']);

		$orgData = $this->organizeData($included);

		return new Person([
			'id' => $data['id'],
			'name' => $data['attributes']['name'],
			'characters' => $orgData['characters'],
			'staff' => $orgData['staff'],
		]);
	}

	protected function organizeData(array $data): array
	{
		$output = [
			'characters' => [
				'main' => [],
				'supporting' => [],
			],
			'staff' => [],
		];

		if (array_key_exists('characterVoices', $data))
		{
			foreach ($data['characterVoices'] as $cv)
			{
				$mcId = $cv['relationships']['mediaCharacter']['data']['id'];

				if ( ! array_key_exists($mcId, $data['mediaCharacters']))
				{
					continue;
				}

				$mc = $data['mediaCharacters'][$mcId];

				$role = $mc['role'];

				$charId = $mc['relationships']['character']['data']['id'];
				$mediaId = $mc['relationships']['media']['data']['id'];

				$existingMedia = array_key_exists($charId, $output['characters'][$role])
					? $output['characters'][$role][$charId]['media']
					: [];

				$relatedMedia = [
					$mediaId => $data['anime'][$mediaId],
				];

				$includedMedia = array_replace_recursive($existingMedia, $relatedMedia);

				uasort($includedMedia, function ($a, $b) {
					return $a['canonicalTitle'] <=> $b['canonicalTitle'];
				});

				$character = $data['characters'][$charId];

				$output['characters'][$role][$charId] = [
					'character' => $character,
					'media' => $includedMedia,
				];
			}
		}

		if (array_key_exists('mediaStaff', $data))
		{
			foreach ($data['mediaStaff'] as $rid => $role)
			{
				$roleName = $role['role'];
				$mediaType = $role['relationships']['media']['data']['type'];
				$mediaId = $role['relationships']['media']['data']['id'];
				$media = $data[$mediaType][$mediaId];
				$output['staff'][$roleName][$mediaType][$mediaId] = $media;
			}
		}

		uasort($output['characters']['main'], function ($a, $b) {
			return $a['character']['canonicalName'] <=> $b['character']['canonicalName'];
		});
		uasort($output['characters']['supporting'], function ($a, $b) {
			return $a['character']['canonicalName'] <=> $b['character']['canonicalName'];
		});
		ksort($output['staff']);
		foreach ($output['staff'] as $role => &$media)
		{
			if (array_key_exists('anime', $media))
			{
				uasort($media['anime'], function ($a, $b) {
					return $a['canonicalTitle'] <=> $b['canonicalTitle'];
				});
			}

			if (array_key_exists('manga', $media))
			{
				uasort($media['manga'], function ($a, $b) {
					return $a['canonicalTitle'] <=> $b['canonicalTitle'];
				});
			}
		}

		return $output;
	}
}