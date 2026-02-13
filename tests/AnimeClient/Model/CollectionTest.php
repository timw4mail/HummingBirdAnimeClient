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
}
