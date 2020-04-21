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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Controller for Anime-related pages
 */
final class History extends BaseController {
	/**
	 * The anime list model
	 * @var AnimeModel
	 */
	protected AnimeModel $animeModel;

	/**
	 * The manga list model
	 * @var MangaModel
	 */
	protected MangaModel $mangaModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->animeModel = $container->get('anime-model');
		$this->mangaModel = $container->get('manga-model');
	}

	public function anime(): void
	{
		// $this->outputJSON($this->animeModel->getHistory());
		// return;
		$this->outputHTML('history/anime', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				'Anime',
				'Watching History'
			),
			'items' => $this->animeModel->getHistory(),
		]);
	}

	public function manga(): void
	{
		$this->outputJSON($this->mangaModel->getHistory());
		return;
		$this->outputHTML('history/manga', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				'Reading History'
			),
			'items' => $this->mangaModel->getHistory(),
		]);
	}
}
