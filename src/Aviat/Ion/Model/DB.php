<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion\Model;

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Model as BaseModel;

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
	 * The config manager
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * The database connection information array
	 * @var array $db_config
	 */
	protected $db_config;

	/**
	 * Constructor
	 *
	 * @param ConfigInterface $config
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->config = $config;
		$this->db_config = (array)$config->get('database');
	}
}
// End of DB.php