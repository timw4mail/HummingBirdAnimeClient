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

namespace Aviat\AnimeClient\Command;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\Controller\Index;

/**
 * Clears out image cache directories, then re-creates the image cache
 * for manga and anime
 */
final class UpdateThumbnails extends ClearThumbnails {
	/**
	 * Model for making requests to Kitsu API
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

	/**
	 * The default controller, which has the method to cache the images
	 */
	protected $controller;

	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));

		$this->controller = new Index($this->container);
		$this->kitsuModel = $this->container->get('kitsu-model');

		// Clear the existing thunbnails
		parent::execute($args, $options);

		$ids = $this->getImageList();

		// Resave the images
		foreach($ids as $type => $typeIds)
		{
			foreach ($typeIds as $id)
			{
				$this->controller->images($type, "{$id}.jpg", FALSE);
			}

			$this->echoBox("Finished regenerating {$type} thumbnails");
		}

		$this->echoBox('Finished regenerating all thumbnails');
	}

	public function getImageList()
	{
		$mangaList = $this->kitsuModel->getFullRawMangaList();
		$includes = JsonAPI::organizeIncludes($mangaList['included']);
		$mangaIds = array_keys($includes['manga']);

		$animeList = $this->kitsuModel->getFullRawAnimeList();
		$includes = JsonAPI::organizeIncludes($animeList['included']);
		$animeIds = array_keys($includes['anime']);

		// print_r($mangaIds);
		// die();

		return [
			'anime' => $animeIds,
			'manga' => $mangaIds,
		];
	}
}