<?php
/**
 * Base DB model
 */
namespace AnimeClient\Base\Model;

use AnimeClient\Base\Container;

/**
 * Base model for database interaction
 */
class DB extends \AnimeClient\Base\Model {
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
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->db_config = $this->config->database;
	}
}
// End of BaseDBModel.php