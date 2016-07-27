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

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Cache\CacheDriverInterface;
use Aviat\Ion\Model\DB;

/**
 * Driver for caching via a traditional SQL database
 */
class SQLDriver extends DB implements CacheDriverInterface  {

	/**
	 * The query builder object
	 * @var object $db
	 */
	protected $db;

	/**
	 * Create the driver object
	 *
	 * @param ConfigInterface $config
	 */
	public function __construct(ConfigInterface $config)
	{
		parent::__construct($config);
		$this->db = \Query($this->db_config['collection']);
	}

	/**
	 * Retrieve a value from the cache backend
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
		
		$this->db->insert('cache');

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

	/**
	 * Clear the contents of the cache
	 *
	 * @return void
	 */
	public function invalidateAll()
	{
		$this->db->truncate('cache');
	}
}
// End of SQLDriver.php