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

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\ListItemInterface;
use Aviat\AnimeClient\Types\FormItemData;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;

/**
 * CRUD operations for Kitsu list items
 */
final class ListItem implements ListItemInterface {
	use ContainerAware;
	use KitsuTrait;

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

		if (array_key_exists('notes', $data))
		{
			$body['data']['attributes']['notes'] = $data['notes'];
		}

		$authHeader = $this->getAuthHeader();

		$request = $this->requestBuilder->newRequest('POST', 'library-entries');

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->setJsonBody($body)
			->getFullRequest();
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
	}

	public function get(string $id): array
	{
		$authHeader = $this->getAuthHeader();

		$request = $this->requestBuilder->newRequest('GET', "library-entries/{$id}")
			->setQuery([
				'include' => 'media,media.categories,media.mappings'
			]);

		if ($authHeader !== FALSE)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		$request = $request->getFullReqest();
		$response = getResponse($request);
		return Json::decode(wait($response->getBody()));
	}

	public function increment(string $id, FormItemData $data): Request
	{
		return $this->update($id, $data);
	}

	public function update(string $id, FormItemData $data): Request
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

	private function getAuthHeader()
	{
		$cache = $this->getContainer()->get('cache');
		$cacheItem = $cache->getItem('kitsu-auth-token');
		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		if ($sessionSegment->get('auth_token') !== NULL) {
			$token = $sessionSegment->get('auth_token');
			return "bearer {$token}";
		}

		if ($cacheItem->isHit()) {
			$token = $cacheItem->get();
			return "bearer {$token}";
		}

		return FALSE;
	}
}