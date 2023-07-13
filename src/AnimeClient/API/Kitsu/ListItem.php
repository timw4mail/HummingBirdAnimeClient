<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\AnimeClient\Types\FormItemData;
use Aviat\Ion\Di\ContainerAware;

use Throwable;

/**
 * CRUD operations for Kitsu list items
 */
final class ListItem extends AbstractListItem
{
	use ContainerAware;
	use RequestBuilderTrait;

	/**
	 * @throws Throwable
	 */
	public function create(array $data): Request
	{
		return $this->requestBuilder->mutateRequest('CreateLibraryItem', [
			'id' => $data['id'],
			'status' => strtoupper($data['status']),
			'type' => strtoupper($data['type']),
			'userId' => $data['user_id'],
		]);
	}

	public function createFull(array $data): Request
	{
		$body = [
			'data' => [
				'type' => 'libraryEntries',
				'attributes' => [
					'status' => $data['status'],
					'progress' => $data['progress'] ?? 0,
				],
				'relationships' => [
					'user' => [
						'data' => [
							'id' => $data['user_id'],
							'type' => 'users',
						],
					],
					'media' => [
						'data' => [
							'id' => $data['id'],
							'type' => $data['type'],
						],
					],
				],
			],
		];

		if (array_key_exists('notes', $data))
		{
			$body['data']['attributes']['notes'] = $data['notes'];
		}

		$authHeader = $this->getAuthHeader();

		$request = $this->requestBuilder->newRequest('POST', 'library-entries');

		if ($authHeader !== NULL)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->setJsonBody($body)
			->getFullRequest();
	}

	/**
	 * @throws Throwable
	 */
	public function delete(string $id): Request
	{
		return $this->requestBuilder->mutateRequest('DeleteLibraryItem', [
			'id' => $id,
		]);
	}

	/**
	 * @throws Throwable
	 * @return mixed[]
	 */
	public function get(string $id): array
	{
		return $this->requestBuilder->runQuery('GetLibraryItem', [
			'id' => $id,
		]);
	}

	/**
	 * Increase the progress on the medium by 1
	 */
	public function increment(string $id, FormItemData $data): Request
	{
		return $this->requestBuilder->mutateRequest('IncrementLibraryItem', [
			'id' => $id,
			'progress' => $data->progress,
		]);
	}

	/**
	 * @throws Throwable
	 */
	public function update(string $id, FormItemData $data): Request
	{
		// Data to always send
		$updateData = [
			'id' => $id,
			'notes' => $data['notes'],
			'private' => (bool) $data['private'],
			'reconsumeCount' => (int) $data['reconsumeCount'],
			'reconsuming' => (bool) $data['reconsuming'],
			'status' => strtoupper($data['status']),
		];

		// Only send these variables if they have a value
		if ($data['progress'] !== NULL)
		{
			$updateData['progress'] = (int) $data['progress'];
		}

		if ($data['ratingTwenty'] !== NULL)
		{
			$updateData['ratingTwenty'] = (int) $data['ratingTwenty'];
		}

		return $this->requestBuilder->mutateRequest('UpdateLibraryItem', $updateData);
	}

	private function getAuthHeader(): ?string
	{
		$auth = $this->getContainer()->get('auth');
		$token = $auth->getAuthToken();

		if ( ! empty($token))
		{
			return "bearer {$token}";
		}

		return NULL;
	}
}
