<?php
/**
 * Base DB model
 */
namespace AnimeClient;

/**
 * Base model for database interaction
 */
class BaseDBModel extends BaseModel {
	/**
	 * The query builder object
	 * @var object $db
	 */
	protected $db;

	/**
	 * The database connection information array
	 * @var array $db_config
	 */
	protected $db_config;

	/**
	 * Constructor
	 */
	public function __construct(Config $config)
	{
		parent::__construct($config);
		$this->db_config = $this->config->database;
	}
}
// End of BaseDBModel.php