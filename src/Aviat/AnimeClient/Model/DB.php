<?php
/**
 * Base DB model
 */
namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model as BaseModel;

/**
 * Base model for database interaction
 */
class DB extends BaseModel {
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
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->db_config = $this->config->get('database');
	}
}
// End of BaseDBModel.php