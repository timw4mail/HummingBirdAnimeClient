<?php

class AnimeController extends BaseController {

	private $model;
	private $collection_model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new AnimeModel();
		$this->collection_model = new AnimeCollectionModel();
	}

	public function index()
	{
		$this->anime_list('currently-watching');
	}

	public function all()
	{
		$data = $this->model->get_all_lists();
		$this->outputHTML('anime_list', [
			'title' => "Tim's Anime List &middot; All",
			'sections' => $data
		]);
	}

	public function anime_list($type, $title="Tim's Anime List")
	{
		$data = $this->model->get_list($type);
		$this->outputHTML('anime_list', [
			'title' => $title,
			'sections' => $data
		]);
	}

	public function collection()
	{
		$this->collection_model->get_collection_seed();

		$this->outputHTML('anime_list', [
			'title' => "Tim's Anime Collection",
			'sections' => []
		]);
	}

	public function login()
	{
		$data = $this->model->authenticate();
		//print_r($data);
	}
}
// End of AnimeController.php