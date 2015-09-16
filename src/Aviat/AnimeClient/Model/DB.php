<?php
/**
 * Base DB model
 */
namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Container;

/**
 * Base model for database interaction
 */
class DB extends \Aviat\AnimeClient\Model {
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
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->db_config = $this->config->database;
	}
}
// End of BaseDBModel.php