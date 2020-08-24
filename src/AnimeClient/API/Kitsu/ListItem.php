<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\AnimeClient\Types\FormItemData;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;

use Throwable;

/**
 * CRUD operations for Kitsu list items
 */
final class ListItem extends AbstractListItem {
	use ContainerAware;
	use RequestBuilderTrait;

	/**
	 * @param array $data
	 * @return Request
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

		if ($authHeader !== NULL)
		{
			$request = $request->setHeader('Authorization', $authHeader);
		}

		return $request->setJsonBody($body)
			->getFullRequest();
	}

	/**
	 * @param string $id
	 * @return Request
	 * @throws Throwable
	 */
	public function delete(string $id): Request
	{
		return $this->requestBuilder->mutateRequest('DeleteLibraryItem', [
			'id' => $id
		]);
	}

	/**
	 * @param string $id
	 * @return array
	 * @throws Throwable
	 */
	public function get(string $id): array
	{
		return $this->requestBuilder->runQuery('GetLibraryItem', [
			'id' => $id,
		]);
	}

	/**
	 * Increase the progress on the medium by 1
	 *
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 */
	public function increment(string $id, FormItemData $data): Request
	{
		return $this->requestBuilder->mutateRequest('IncrementLibraryItem', [
			'id' => $id,
			'progress' => $data->progress
		]);
	}

	/**
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 * @throws Throwable
	 */
	public function update(string $id, FormItemData $data): Request
	{
		return $this->requestBuilder->mutateRequest('UpdateLibraryItem', [
			'id' => $id,
			'notes' => $data['notes'],
			'private' => (bool)$data['private'],
			'progress' => (int)$data['progress'],
			'ratingTwenty' => (int)$data['ratingTwenty'],
			'reconsumeCount' => (int)$data['reconsumeCount'],
			'reconsuming' => (bool)$data['reconsuming'],
			'status' => strtoupper($data['status']),
		]);
	}

	/**
	 * @return bool|string
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	private function getAuthHeader(): ?string
	{
		$auth = $this->getContainer()->get('auth');
		$token = $auth->getAuthToken();

		if ( ! empty($token)) {
			return "bearer {$token}";
		}

		return NULL;
	}
}