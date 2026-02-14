<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Collection;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;

/**
 * @internal
 */
final class CollectionTest extends AnimeClientTestCase
{
	public function testInvalidDb(): void
	{
		$invalidDbFile = self::TEST_DATA_DIR . '/invalid.sqlite';
		file_put_contents($invalidDbFile, 'not a sqlite file');

		$this->container->get('config')->set('database', [
			'type' => 'sqlite',
			'file' => $invalidDbFile,
		]);

		$model = new class($this->container) extends Collection {};
		$friend = new Friend($model);

		$this->assertNull($friend->db);

		unlink($invalidDbFile);
	}

	public function testValidDb(): void
	{
		$model = new class($this->container) extends Collection {};
		$friend = new Friend($model);

		$this->assertNotNull($friend->db);
	}

	public function testInit(): void
	{
		$model = new class($this->container) extends Collection {};
		$friend = new Friend($model);

		// Test init with different DB types
		$friend->dbConfig = [
			'type' => 'mysql',
			'host' => 'localhost',
			'user' => 'root',
			'pass' => 'root',
			'name' => 'test',
		];
		$friend->init();
		// It falls back to memory sqlite on PDOException
		$this->assertNotNull($friend->db);

		$invalidDbFile = self::TEST_DATA_DIR . '/not-sqlite.sqlite';
		file_put_contents($invalidDbFile, 'not a sqlite file');
		$friend->dbConfig = [
			'type' => 'sqlite',
			'file' => $invalidDbFile,
		];
		$friend->init();
		$this->assertNull($friend->db);

		unlink($invalidDbFile);
	}
}
