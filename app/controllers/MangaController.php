<?php

class MangaController extends BaseController {

	private $model;

	public function __construct()
	{
		parent::__construct();
		$this->model = new MangaModel();
	}

	public function index()
	{
		$this->manga_list('Reading');
	}

	public function all()
	{
		$data = $this->model->get_all_lists();
		$this->outputHTML('manga_list', [
			'title' => "Tim's Manga List &middot; All",
			'sections' => $data
		]);
	}

	public function manga_list($type, $title="Tim's Manga List")
	{
		$data = $this->model->get_list($type);
		$this->outputHTML('manga_list', [
			'title' => $title,
			'sections' => [$type => $data]
		]);
	}

	public function login()
	{
		$data = $this->model->authenticate();
		//print_r($data);
	}
}
// End of MangaController.php