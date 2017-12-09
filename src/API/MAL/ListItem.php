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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Amp\Artax\{FormBody, Request};
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

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function create(array $data, string $type = 'anime'): Request
	{
		$id = $data['id'];
		$createData = [
			'id' => $id,
			'data' => XML::toXML([
				'entry' => $data['data']
			])
		];

		$config = $this->container->get('config');

		return $this->requestBuilder->newRequest('POST', "{$type}list/add/{$id}.xml")
			->setFormFields($createData)
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();
	}

	/**
	 * Delete a list item
	 *
	 * @param string $id
	 * @param string $type
	 * @return Request
	 */
	public function delete(string $id, string $type = 'anime'): Request
	{
		$config = $this->container->get('config');

		return $this->requestBuilder->newRequest('DELETE', "{$type}list/delete/{$id}.xml")
			->setFormFields([
				'id' => $id
			])
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();

		// return $response->getBody() === 'Deleted'
	}

	public function get(string $id): array
	{
		return [];
	}

	/**
	 * Update a list item
	 *
	 * @param string $id
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function update(string $id, array $data, string $type = 'anime'): Request
	{
		$config = $this->container->get('config');

		$xml = XML::toXML(['entry' => $data]);
		$body = new FormBody();
		$body->addField('id', $id);
		$body->addField('data', $xml);

		return $this->requestBuilder->newRequest('POST', "{$type}list/update/{$id}.xml")
			->setFormFields([
				'id' => $id,
				'data' => $xml
			])
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();
	}
}