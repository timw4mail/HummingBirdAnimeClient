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

use Amp\Artax\Request;
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

	public function create(array $data): Request
	{
		$id = $data['id'];
		$createData = [
			'id' => $id,
			'data' => XML::toXML([
				'entry' => $data['data']
			])
		];

		$config = $this->container->get('config');

		return $this->requestBuilder->newRequest('POST', "animelist/add/{$id}.xml")
			->setFormFields($createData)
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();

		/* $response = $this->getResponse('POST', "animelist/add/{$id}.xml", [
			'body' => $this->fixBody((new FormBody)->addFields($createData))
		]);

		return $response->getBody() === 'Created'; */
	}

	public function delete(string $id): Request
	{
		$config = $this->container->get('config');

		return $this->requestBuilder->newRequest('DELETE', "animelist/delete/{$id}.xml")
			->setFormFields([
				'id' => $id
			])
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();

		/*$response = $this->getResponse('DELETE', "animelist/delete/{$id}.xml", [
			'body' => $this->fixBody((new FormBody)->addField('id', $id))
		]);

		return $response->getBody() === 'Deleted';*/
	}

	public function get(string $id): array
	{
		return [];
	}

	public function update(string $id, array $data): Request
	{
		$config = $this->container->get('config');

		$xml = XML::toXML(['entry' => $data]);
		$body = (new FormBody)
			->addField('id', $id)
			->addField('data', $xml);

		return $this->requestBuilder->newRequest('POST', "animelist/update/{$id}.xml")
			->setFormFields([
				'id' => $id,
				'data' => $xml
			])
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal', 'password']))
			->getFullRequest();

		/* return $this->getResponse('POST', "animelist/update/{$id}.xml", [
			'body' => $this->fixBody($body)
		]); */
	}
}