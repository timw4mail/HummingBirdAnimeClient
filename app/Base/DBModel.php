<?php
/**
 * Base DB model
 */
namespace AnimeClient\Base;

/**
 * Base model for database interaction
 */
class DBModel extends Model {
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