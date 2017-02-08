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

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use RuntimeException;

/**
 * CRUD operations for Kitsu list items
 */
class ListItem extends AbstractListItem {
	use ContainerAware;
	use KitsuTrait;

	public function __construct()
	{
		$this->init();
	}

	public function create(array $data): bool
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
		
		$request = $this->requestBuilder->newRequest('POST', 'library-entries')
			->setJsonBody($body)
			->getFullRequest();
		$response = $this->getResponse('POST', 'library-entries', [
			'body' => Json::encode($body)
		]);

		return ($response->getStatusCode() === 201);
	}

	public function delete(string $id): bool
	{
		$response = $this->getResponse('DELETE', "library-entries/{$id}");
		return ($response->getStatusCode() === 204);
	}

	public function get(string $id): array
	{
		$request = $this->requestBuilder->newRequest('GET', "library-entries/{$id}")
			->setQuery([
				'include' => 'media,media.genres,media.mappings'
			])
			->getFullRequest();
		/*return $this->getRequest("library-entries/{$id}", [
			'query' => [
				'include' => 'media,media.genres,media.mappings'
			]
		]);*/
		
		$response = \Amp\wait((new \Amp\Artax\Client)->request($request));
		return Json::decode($response->getBody());
	}

	public function update(string $id, array $data): Response
	{
		$requestData = [
			'data' => [
				'id' => $id,
				'type' => 'libraryEntries',
				'attributes' => $data
			]
		];

		$response = $this->getResponse('PATCH', "library-entries/{$id}", [
			'body' => JSON::encode($requestData)
		]);

		return $response;
	}
}