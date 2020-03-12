<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
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
