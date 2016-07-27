<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */
namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Model;

/**
 * Base model for database interaction
 */
class DB extends Model {
	use \Aviat\Ion\Di\ContainerAware;

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
		$this->db_config = $container->get('config')->get('database');
		$this->setContainer($container);
	}
}
// End of DB.php