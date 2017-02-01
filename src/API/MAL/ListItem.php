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

namespace Aviat\AnimeClient\API\MAL;

use Amp\Artax\FormBody;
use Aviat\AnimeClient\API\{
	AbstractListItem,
	XML
};
use Aviat\Ion\Di\ContainerAware;

/**
 * CRUD operations for MAL list items
 */
class ListItem {
	use ContainerAware;
	use MALTrait;

	public function create(array $data): bool
	{
		$id = $data['id'];
		$body = (new FormBody)
			->addField('id', $data['id'])
			->addField('data', XML::toXML(['entry' => $data['data']]));
		$response = $this->getResponse('POST', "animelist/add/{$id}.xml", [
			'headers' => [
				'Content-type' => 'application/x-www-form-urlencoded',
				'Accept' => 'text/plain'
			],
			'body' => $body
		]);
		
		return $response->getStatus() === 201;
	}

	public function delete(string $id): bool
	{
		$response = $this->getResponse('DELETE', "animeclient/delete/{$id}.xml", [
			'body' => (new FormBody)->addField('id', $id)
		]);
		
		return $response->getBody() === 'Deleted'; 
	}

	public function get(string $id): array
	{
		return [];
	}

	public function update(string $id, array $data): Response
	{
		$body = (new FormBody)
			->addField('id', $id)
			->addField('data', XML::toXML(['entry' => $data]))
			
		return $this->postRequest("animelist/update/{$id}.xml", [
			'headers' => [
				'Content-type' => 'application/x-www-form-urlencoded',
				'Accept' => 'text/plain'
			],
			'body' => $body
		]);
	}
}