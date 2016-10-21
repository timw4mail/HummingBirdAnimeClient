<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aviat\AnimeClient\Util;

/**
 * Generates thumbnail image cache so that cover images load faster
 */
class CacheImages extends BaseCommand {

	/**
	 * Manga Model
	 *
	 * @var Aviat\AnimeClient\Model\Manga
	 */
	protected $mangaModel;

	/**
	 * Anime Model
	 *
	 * @var Aviat\AnimeClient\Model\Anime
	 */
	protected $animeModel;

	/**
	 * Miscellaneous helper methods
	 *
	 * @var Aviat\AnimeClient\Util
	 */
	protected $util;

	/**
	 * Convert manga images
	 *
	 * @throws \ConsoleKit\ConsoleException
	 * @return void
	 */
	protected function getMangaImages()
	{
		$raw_list = $this->mangaModel->_get_list_from_api();
		$manga_list = array_column($raw_list, 'manga');

		$total = count($raw_list);
		$current = 0;
		foreach($manga_list as $item)
		{
			$this->util->get_cached_image($item['poster_image'], $item['id'], 'manga');
			$current++;

			echo "Cached {$current} of {$total} manga images. \n";
		}
	}

	/**
	 * Convert anime images
	 *
	 * @throws \ConsoleKit\ConsoleException
	 * @return void
	 */
	protected function getAnimeImages()
	{
		$raw_list = $this->animeModel->get_raw_list();

		$total = count($raw_list);
		$current = 0;
		foreach($raw_list as $item)
		{
			$this->util->get_cached_image($item['anime']['cover_image'], $item['anime']['slug'], 'anime');
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
	public function execute(array $args, array $options = [])
	{
		$this->setContainer($this->setupContainer());
		$this->util = new Util($this->container);
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