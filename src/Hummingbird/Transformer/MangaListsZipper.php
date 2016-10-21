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

namespace Aviat\AnimeClient\Hummingbird\Transformer;

/**
 * Merges the two separate manga lists together
 */
class MangaListsZipper {

	/**
	 * List of manga information
	 *
	 * @var array
	 */
	protected $manga_series_list = [];

	/**
	 * List of manga tracking information
	 *
	 * @var array
	 */
	protected $manga_tracking_list = [];

	/**
	 * Create the transformer
	 *
	 * @param array $merge_lists The raw manga data
	 */
	public function __construct(array $merge_lists)
	{
		$this->manga_series_list = $merge_lists['manga'];
		$this->manga_tracking_list = $merge_lists['manga_library_entries'];
	}

	/**
	 * Do the transformation, and return the output
	 *
	 * @return array
	 */
	public function transform()
	{
		$this->index_manga_entries();

		$output = [];

		foreach ($this->manga_tracking_list as &$entry)
		{
			$id = $entry['manga_id'];
			$entry['manga'] = $this->manga_series_list[$id];
			unset($entry['manga_id']);

			$output[] = $entry;
		}

		return $output;
	}

	/**
	 * Index manga series by the id
	 *
	 * @return void
	 */
	protected function index_manga_entries()
	{
		$orig_list = $this->manga_series_list;
		$indexed_list = [];

		foreach ($orig_list as $manga)
		{
			$id = $manga['id'];
			$indexed_list[$id] = $manga;
		}

		$this->manga_series_list = $indexed_list;
	}

}
// End of ManagListsZipper.php