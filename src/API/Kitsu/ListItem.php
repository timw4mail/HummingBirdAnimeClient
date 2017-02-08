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

use const Aviat\AnimeClient\SESSION_SEGMENT;

use Amp\Artax\Request;
use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use RuntimeException;

/**
 * CRUD operations for Kitsu list items
 */
class ListItem extends AbstractListItem {
	use ContainerAware;
	use KitsuTrait;

	private function getAuthHeader()
	{
		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		if ($sessionSegment->get('auth_token') !== null)
		{
			$token = $sessionSegment->get('auth_token');
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
		$request = $this->requestBuilder->newRequest('GET', "library-entries/{$id}")
			->setQuery([
				'include' => 'media,media.genres,media.mappings'
			])
			->getFullRequest();

		$response = \Amp\wait((new \Amp\Artax\Client)->request($request));
		return Json::decode($response->getBody());
	}

	public function update(string $id, array $data): Request
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