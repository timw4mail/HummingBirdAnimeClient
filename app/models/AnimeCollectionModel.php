<?php

/**
 * Model for getting anime collection data
 */
class AnimeCollectionModel extends BaseModel {

	private $db;
	private $anime_model;
	private $db_config;

	public function __construct()
	{
		$this->db_config = require_once(__DIR__ . '/../config/database.php');
		$this->db = Query($this->db_config['collection']);

		$this->anime_model = new AnimeModel();

		//parent::__construct();
	}

	public function get_collection()
	{
		// TODO: show collection from database
	}

}
// End of AnimeCollectionModel.php