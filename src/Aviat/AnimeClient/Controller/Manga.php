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
namespace Aviat\AnimeClient\Controller;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Hummingbird\Enum\MangaReadingStatus;
use Aviat\AnimeClient\Hummingbird\Transformer\MangaListTransformer;

/**
 * Controller for manga list
 */
class Manga extends Controller {

	use \Aviat\Ion\StringWrapper;

	/**
	 * The manga model
	 * @var object $model
	 */
	protected $model;

	/**
	 * Data to ve sent to all routes in this controller
	 * @var array $base_data
	 */
	protected $base_data;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->model = $container->get('manga-model');
		$this->base_data = array_merge($this->base_data, [
			'menu_name' => 'manga_list',
			'config' => $this->config,
			'url_type' => 'manga',
			'other_type' => 'anime'
		]);
	}

	/**
	 * Get a section of the manga list
	 *
	 * @param string $status
	 * @param string $view
	 * @return void
	 */
	public function index($status = "all", $view = "")
	{
		$map = [
			'all' => 'All',
			'plan_to_read' => MangaModel::PLAN_TO_READ,
			'reading' => MangaModel::READING,
			'completed' => MangaModel::COMPLETED,
			'dropped' => MangaModel::DROPPED,
			'on_hold' => MangaModel::ON_HOLD
		];

		$title = $this->config->get('whose_list') . "'s Manga List &middot; {$map[$status]}";

		$view_map = [
			'' => 'cover',
			'list' => 'list'
		];

		$data = ($status !== 'all')
			? [$map[$status] => $this->model->get_list($map[$status])]
			: $this->model->get_all_lists();

		$this->outputHTML('manga/' . $view_map[$view], [
			'title' => $title,
			'sections' => $data,
		]);
	}

	/**
	 * Form to add an manga
	 *
	 * @return void
	 */
	public function add_form()
	{
		$raw_status_list = MangaReadingStatus::getConstList();

		$statuses = [];

		foreach ($raw_status_list as $status_item)
		{
			$statuses[$status_item] = (string)$this->string($status_item)
				->underscored()
				->humanize()
				->titleize();
		}

		$this->set_session_redirect();
		$this->outputHTML('manga/add', [
			'title' => $this->config->get('whose_list') .
				"'s Manga List &middot; Add",
			'action_url' => $this->urlGenerator->url('manga/add'),
			'status_list' => $statuses
		]);
	}

	/**
	 * Add an manga to the list
	 *
	 * @return void
	 */
	public function add()
	{
		$data = $this->request->post->get();
		if ( ! array_key_exists('id', $data))
		{
			$this->redirect("manga/add", 303);
		}

		$result = $this->model->add($data);

		if ($result['statusCode'] >= 200 && $result['statusCode'] < 300)
		{
			$this->set_flash_message('Added new manga to list', 'success');
		}
		else
		{
			$this->set_flash_message('Failed to add new manga to list' . $result['body'] , 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Show the manga edit form
	 *
	 * @param string $id
	 * @param string $status
	 * @return void
	 */
	public function edit($id, $status = "All")
	{
		$this->set_session_redirect();
		$item = $this->model->get_library_item($id, $status);
		$title = $this->config->get('whose_list') . "'s Manga List &middot; Edit";

		$this->outputHTML('manga/edit', [
			'title' => $title,
			'status_list' => MangaReadingStatus::getConstList(),
			'item' => $item,
			'action' => $this->container->get('url-generator')
				->url('/manga/update_form'),
		]);
	}

	/**
	 * Search for a manga to add to the list
	 *
	 * @param  string $query
	 * @return void
	 */
 	public function search()
 	{
 		$query = $this->request->query->get('query');
 		$this->outputJSON($this->model->search($query));
 	}

	/**
	 * Update an anime item via a form submission
	 *
	 * @return void
	 */
	public function form_update()
	{
		$post_data = $this->request->post->get();

		// Do some minor data manipulation for
		// large form-based updates
		$transformer = new MangaListTransformer();
		$post_data = $transformer->untransform($post_data);
		$full_result = $this->model->update($post_data);

		$result = $full_result['body'];

		if (array_key_exists('manga', $result))
		{
			$m =& $result['manga'][0];
			$title = ( ! empty($m['english_title']))
				? "{$m['romaji_title']} ({$m['english_title']})"
				: "{$m['romaji_title']}";

			$this->set_flash_message("Successfully updated {$title}.", 'success');
		}
		else
		{
			$this->set_flash_message('Failed to update manga.', 'error');
		}

		$this->session_redirect();
	}

	/**
	 * Update an anime item
	 *
	 * @return boolean|null
	 */
	public function update()
	{
		$result = $this->model->update($this->request->post->get());
		$this->outputJSON($result['body'], $result['statusCode']);
	}
}
// End of MangaController.php