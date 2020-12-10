<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aviat\AnimeClient\API\Kitsu\Model as KitsuModel;
use Aviat\AnimeClient\Controller\Images;

/**
 * Clears out image cache directories, then re-creates the image cache
 * for manga and anime
 */
final class UpdateThumbnails extends ClearThumbnails {
	/**
	 * Model for making requests to Kitsu API
	 * @var KitsuModel
	 */
	protected KitsuModel $kitsuModel;

	/**
	 * The default controller, which has the method to cache the images
	 */
	protected Images $controller;

	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));

		$this->controller = new Images($this->container);
		$this->kitsuModel = $this->container->get('kitsu-model');

		// Clear the existing thumbnails
		parent::execute($args, $options);

		$ids = $this->getImageList();

		// Resave the images
		foreach($ids as $type => $typeIds)
		{
			foreach ($typeIds as $id)
			{
				$this->controller->cache($type, "{$id}.jpg", FALSE);
			}

			$this->echoBox("Finished regenerating {$type} thumbnails");
		}

		$this->echoBox('Finished regenerating all thumbnails');
	}

	/**
	 * @return array array-key[][]
	 * @psalm-return array{anime: list<array-key>, manga: list<array-key>}
	 */
	public function getImageList(): array
	{
		$animeIds = array_map(
			static fn ($item) => $item['media']['id'],
			$this->kitsuModel->getThumbList('ANIME')
		);
		$mangaIds = array_map(
			static fn ($item) => $item['media']['id'],
			$this->kitsuModel->getThumbList('MANGA')
		);

		return [
			'anime' => $animeIds,
			'manga' => $mangaIds,
		];
	}
}