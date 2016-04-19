<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Command;

use \ConsoleKit\Widgets\Box;

use Aviat\AnimeClient\Model;
/**
 * Generates thumbnail image cache so that cover images load faster
 */
class CacheImages extends BaseCommand {

	protected $mangaModel;
	protected $animeModel;
	protected $model;

	/**
	 * Echo text in a box
	 *
	 * @param string $message
	 * @return void
	 */
	protected function echoBox($message)
	{
		echo "\n";
		$box = new Box($this->getConsole(), $message);
		$box->write();
		echo "\n";
	}

	/*
	 * Convert manga images
	 *
	 * @throws \ConsoleKit\ConsoleException
	 */
	protected function getMangaImages()
	{
		$raw_list = $this->mangaModel->_get_list_from_api();
		$manga_list = array_column($raw_list, 'manga');

		$total = count($raw_list);
		$current = 0;
		foreach($manga_list as $item)
		{
			$this->model->get_cached_image($item['poster_image'], $item['id'], 'manga');
			$current++;

			echo "Cached {$current} of {$total} manga images. \n";
		}
	}

	/**
	 * Convert anime images
	 *
	 * @throws \ConsoleKit\ConsoleException
	 */
	protected function getAnimeImages()
	{
		$raw_list = $this->animeModel->get_raw_list();

		$total = count($raw_list);
		$current = 0;
		foreach($raw_list as $item)
		{
			$this->model->get_cached_image($item['anime']['cover_image'], $item['anime']['slug'], 'anime');
			$current++;

			echo "Cached {$current} of {$total} anime images. \n";
		}
	}

	/**
	 * Run the image conversion script
	 *
	 * @param array $args
	 * @param array $options
	 * @return void
	 * @throws \ConsoleKit\ConsoleException
	 */
	public function execute(array $args, array $options = array())
	{
		$this->setContainer($this->setupContainer());
		$this->model = new Model($this->container);
		$this->animeModel = $this->container->get('anime-model');
		$this->mangaModel = $this->container->get('manga-model');

		$this->echoBox('Starting image conversion');

		$this->echoBox('Converting manga images');
		$this->getMangaImages();

		$this->echoBox('Converting anime images');
		$this->getAnimeImages();

		$this->echoBox('Finished image conversion');
	}
}
// End of CacheImages.php