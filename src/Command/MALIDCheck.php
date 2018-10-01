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

use const Aviat\AnimeClient\MILLI_FROM_NANO;
use const Aviat\AnimeClient\SRC_DIR;

use function Amp\Promise\wait;

use Aviat\AnimeClient\API\{
	APIRequestBuilder,
	JsonAPI,
	ParallelAPIRequest
};

use Aviat\Ion\Json;


final class MALIDCheck extends BaseCommand {

	private $kitsuModel;

	/**
	 * Check MAL mapping validity
	 *
	 * @param array $args
	 * @param array $options
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 * @throws \Throwable
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));
		$this->kitsuModel = $this->container->get('kitsu-model');

		$kitsuAnimeIdList = $this->formatKitsuList('anime');
		$animeCount = count($kitsuAnimeIdList);
		$this->echoBox("{$animeCount} mappings for Anime");
		$animeMappings = $this->checkMALIds($kitsuAnimeIdList, 'anime');
		$this->mappingStatus($animeMappings, $animeCount, 'anime');

		$kitsuMangaIdList = $this->formatKitsuList('manga');
		$mangaCount = count($kitsuMangaIdList);
		$this->echoBox("{$mangaCount} mappings for Manga");
		$mangaMappings = $this->checkMALIds($kitsuMangaIdList, 'manga');
		$this->mappingStatus($mangaMappings, $mangaCount, 'manga');

		$publicDir = realpath(SRC_DIR . '/../public') . '/';
		file_put_contents($publicDir . 'mal_mappings.json', Json::encode([
			'anime' => $animeMappings,
			'manga' => $mangaMappings,
		]));

		$this->echoBox('Mapping file saved to "' . $publicDir . 'mal_mappings.json' . '"');
	}

	/**
	 * Format a kitsu list for the sake of comparision
	 *
	 * @param string $type
	 * @return array
	 */
	private function formatKitsuList(string $type = 'anime'): array
	{
		$options = [
			'include' => 'media,media.mappings',
		];
		$data = $this->kitsuModel->{'getFullRaw' . ucfirst($type) . 'List'}($options);

		if (empty($data))
		{
			return [];
		}

		$includes = JsonAPI::organizeIncludes($data['included']);

		// Only bother with mappings from MAL that are of the specified media type
		$includes['mappings'] = array_filter($includes['mappings'], function ($mapping) use ($type) {
			return $mapping['externalSite'] === "myanimelist/{$type}";
		});

		$output = [];

		foreach ($data['data'] as $listItem)
		{
			$id = $listItem['relationships']['media']['data']['id'];
			$mediaItem = $includes[$type][$id];

			// Set titles
			$listItem['titles'] = $mediaItem['titles'];

			$potentialMappings = $mediaItem['relationships']['mappings'];
			$malId = NULL;

			foreach ($potentialMappings as $mappingId)
			{
				if (array_key_exists($mappingId, $includes['mappings']))
				{
					$malId = $includes['mappings'][$mappingId]['externalId'];
				}
			}

			// Skip to the next item if there isn't a MAL ID
			if ($malId === NULL)
			{
				continue;
			}

			// Group by malIds to simplify lookup of media details
			// for checking validity of the malId mappings
			$output[$malId] = $listItem;
		}

		ksort($output);

		return $output;
	}

	/**
	 * Check for valid Kitsu -> MAL mapping
	 *
	 * @param array $kitsuList
	 * @param string $type
	 * @return array
	 * @throws \Throwable
	 */
	private function checkMALIds(array $kitsuList, string $type): array
	{
		$goodMappings = [];
		$badMappings = [];
		$suspectMappings = [];

		$responses = $this->makeMALRequests(array_keys($kitsuList), $type);

		// If the page returns a 404, put it in the bad mappings list
		// otherwise, do a search against the titles, to see if the mapping
		// seems valid
		foreach($responses as $id => $response)
		{
			$body = wait($response->getBody());
			$titles = $kitsuList[$id]['titles'];

			if ($response->getStatus() === 404)
			{
				dump($titles);
				die();
				$badMappings[$id] = $titles;
			}
			else
			{
				$titleMatches = FALSE;

				// Attempt to determine if the id matches
				// By searching for a matching title
				foreach($titles as $title)
				{
					if (empty($title))
					{
						continue;
					}

					if (mb_stripos($body, $title) !== FALSE)
					{
						// echo "MAL id {$id} seems to match \"{$title}\"\n";

						$titleMatches = TRUE;
						$goodMappings[$id] = $title;

						// Continue on outer loop
						continue 2;
					}
				}

				if ( ! $titleMatches)
				{
					$suspectMappings[$id] = $titles;
				}
				else
				{
					$goodMappings[$id] = $titles;
				}
			}
		}

		return [
			'good' => $goodMappings,
			'bad' => $badMappings,
			'suspect' => $suspectMappings,
		];
	}

	private function makeMALRequests(array $ids, string $type): array
	{
		$baseUrl = "https://myanimelist.net/{$type}/";

		$requestChunks = array_chunk($ids, 10, TRUE);
		$responses = [];

		// Chunk parallel requests so that we don't hit rate
		// limiting, and get spurious 404 HTML responses
		foreach($requestChunks as $idChunk)
		{
			$requester = new ParallelAPIRequest();

			foreach($idChunk as $id)
			{
				$request = APIRequestBuilder::simpleRequest($baseUrl . $id);
				echo "Checking {$baseUrl}{$id} \n";
				$requester->addRequest($request, (string)$id);
			}

			foreach($requester->getResponses() as $id => $response)
			{
				$responses[$id] = $response;
			}

			echo "Finished checking chunk of 10 entries\n";

			// Rate limiting is annoying :(
			// sleep(1);
			// time_nanosleep(0,  250 * MILLI_FROM_NANO);
		}

		return $responses;
	}

	private function mappingStatus(array $mapping, int $count, string $type): void
	{
		$good = count($mapping['good']);
		$bad = count($mapping['bad']);
		$suspect = count($mapping['suspect']);

		$uType = ucfirst($type);

		$this->echoBox("{$uType} mappings: {$good}/{$count} Good, {$suspect}/{$count} Suspect, {$bad}/{$count} Broken");
	}
}