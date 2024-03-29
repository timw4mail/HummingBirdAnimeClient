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

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\{Controller as BaseController, Model};
use Aviat\Ion\Attribute\{Controller, Route};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};

/**
 * Controller for Anime-related pages
 */
#[Controller]
final class History extends BaseController
{
	/**
	 * The anime list model
	 */
	protected Model\Anime $animeModel;

	/**
	 * The manga list model
	 */
	protected Model\Manga $mangaModel;

	/**
	 * Constructor
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->animeModel = $container->get('anime-model');
		$this->mangaModel = $container->get('manga-model');
	}

	#[Route('history', '/history/{type}')]
	public function index(string $type = 'anime'): void
	{
		if (method_exists($this, $type))
		{
			$this->{$type}();

			return;
		}

		$this->notFound(
			$this->config->get('whose_list') .
			"'s List &middot; History &middot; " .
			'History Not Found',
			'History Not Found'
		);
	}

	private function anime(): void
	{
		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'anime_list',
			'other_type' => 'manga',
			'url_type' => 'anime',
		]);

		$this->outputHTML('history', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Anime List",
				'Anime',
				'Watching History'
			),
			'items' => $this->animeModel->getHistory(),
		]);
	}

	private function manga(): void
	{
		$this->baseData = array_merge($this->baseData, [
			'menu_name' => 'manga_list',
			'other_type' => 'anime',
			'url_type' => 'manga',
		]);

		$this->outputHTML('history', [
			'title' => $this->formatTitle(
				$this->config->get('whose_list') . "'s Manga List",
				'Manga',
				'Reading History'
			),
			'items' => $this->mangaModel->getHistory(),
		]);
	}
}
