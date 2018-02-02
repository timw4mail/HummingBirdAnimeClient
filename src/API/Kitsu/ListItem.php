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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use function Amp\Promise\wait;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\{
	HummingbirdClient,
	ListItemInterface
};
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;

/**
 * CRUD operations for Kitsu list items
 */
class ListItem implements ListItemInterface {
	use ContainerAware;
	use KitsuTrait;

	private function getAuthHeader()
	{
		$cache = $this->getContainer()->get('cache');
		$cacheItem = $cache->getItem('kitsu-auth-token');
		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		if ($sessionSegment->get('auth_token') !== NULL)
		{
			$token = $sessionSegment->get('auth_token');
			return "bearer {$token}";
		}

		if ($cacheItem->isHit())
		{
			$token = $cacheItem->get();
			return "bearer {$token}";
		}

		return FALSE;
	}

	public function create(array $data): Request
	{
		$body = [
			'data' => [
				'type' => 'libraryEntries',
				'attributes' => [
					'status' => $data['status'],
					'progress' => $data['progress'] ?? 0
				],
				'relationships' => [
					'user' => [
						'data' => [
							'id' => $data['user_id'],
							'type' => 'users'
						]
					],
					'media' => [
						'data' => [
							'id' => $data['id'],
							'type' => $data['type']
						]
					]
				]
			]
		];

		$authHeader = $this->getAuthHeader();

		$request = $this->requestBuilder->newRequest('POST', 'library-entries');

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->setJsonBody($body)
			->getFullRequest();

		// return ($response->getStatus() === 201);
	}

	public function delete(string $id): Request
	{
		$authHeader = $this->getAuthHeader();
		$request = $this->requestBuilder->newRequest('DELETE', "library-entries/{$id}");

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->getFullRequest();

		// return ($response->getStatus() === 204);
	}

	public function get(string $id): array
	{
		$authHeader = $this->getAuthHeader();

		$request = $this->requestBuilder->newRequest('GET', "library-entries/{$id}")
			->setQuery([
				'include' => 'media,media.genres,media.mappings'
			]);

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		$request = $request->getFullRequest();

		$response = wait((new HummingbirdClient)->request($request));
		return Json::decode(wait($response->getBody()));
	}

	public function update(string $id, array $data): Request
	{
		$authHeader = $this->getAuthHeader();
		$requestData = [
			'data' => [
				'id' => $id,
				'type' => 'libraryEntries',
				'attributes' => $data
			]
		];

		$request = $this->requestBuilder->newRequest('PATCH', "library-entries/{$id}")
			->setJsonBody($requestData);

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->getFullRequest();
	}
}