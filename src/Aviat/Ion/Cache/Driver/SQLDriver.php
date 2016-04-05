<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package	 Ion
 * @author	  Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license	 MIT
 */

namespace Aviat\Ion\Cache\Driver;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use Aviat\AnimeClient\Model\DB;

/**
 * Driver for caching via a traditional SQL database
 */
class SQLDriver extends DB implements \Aviat\Ion\Cache\CacheDriverInterface  {

	/**
	 * The query builder object
	 * @var object $db
	 */
	protected $db;
	
	/**
	 * Create the driver object
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
	
		try
		{
			$this->db = \Query($this->db_config['collection']);
		}
		catch (\PDOException $e)
		{
			$this->valid_database = FALSE;
			return FALSE;
		}
	}

	/**
	 * Retreive a value from the cache backend
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$query = $this->db->select('value')
			->from('cache')
			->where('key', $key)
			->get();
			
		$row = $query->fetch(\PDO::FETCH_ASSOC);
		if ( ! empty($row))
		{
			return unserialize($row['value']);
		}
		
		return NULL;
	}
	
	/**
	 * Set a cached value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return CacheDriverInterface
	 */
	public function set($key, $value)
	{
		$this->db->set([
			'key' => $key,
			'value' => serialize($value),
		]);
		
		/*if ( ! empty($this->get($key)))
		{
			$this->db->update('cache');
		}
		else*/
		{
			$this->db->insert('cache');
		}

		return $this;
	}
	
	/**
     * Invalidate a cached value
     *
     * @param string $key
     * @return CacheDriverInterface
     */
	public function invalidate($key)
	{
		$this->db->where('key', $key)
			->delete('cache');
			
		return $this;
	}
}
// End of SQLDriver.php