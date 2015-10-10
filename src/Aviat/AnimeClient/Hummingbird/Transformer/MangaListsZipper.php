<?php

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