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

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;

/**
 * Model for getting anime collection data
 */
final class MangaCollection extends Collection {

	/**
	 * Manga API Model
	 * @var Manga $mangaModel
	 */
	protected $mangaModel;

	/**
	 * Create the collection model
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->mangaModel = $container->get('manga-model');
	}
}
// End of MangaCollectionModel.php