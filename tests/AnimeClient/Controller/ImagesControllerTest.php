<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Images as ImagesController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class ImagesControllerTest extends AnimeClientTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		// Create Request/Response Objects
		$GLOBALS['_SERVER']['HTTP_REFERER'] = '';
		$this->setSuperGlobals([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $GLOBALS['_SERVER'],
			'_FILES' => [],
		]);
	}

	public function testCache(): void
	{
		$controller = new ImagesController($this->container);

		ob_start();
		$controller->cache('anime', '123.jpg');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testCacheManga(): void
	{
		$controller = new ImagesController($this->container);

		ob_start();
		$controller->cache('manga', '456.jpg');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testCacheAvatars(): void
	{
		$controller = new ImagesController($this->container);

		ob_start();
		$controller->cache('avatars', '123.jpg');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testCacheCharacters(): void
	{
		$controller = new ImagesController($this->container);

		ob_start();
		$controller->cache('characters', '123.jpg');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testCachePeople(): void
	{
		$controller = new ImagesController($this->container);

		ob_start();
		$controller->cache('people', '123.jpg');
		ob_end_clean();

		$this->assertTrue(true);
	}
}
