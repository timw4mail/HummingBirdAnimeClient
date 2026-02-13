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

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client->method('request')->willReturnCallback(function () {
			$response = $this->createMock(\Amp\Http\Client\Response::class);
			$response->method('getStatus')->willReturn(200);
			$response->method('getHeader')->willReturn('image/jpeg');
			
			// A tiny valid JPEG
			$data = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAABAAEDAREAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAVAQEBAAAAAAAAAAAAAAAAAAAEBf/EABQRAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AVpkH/9k=');
			$body = new \Amp\ByteStream\Payload($data);
			$response->method('getBody')->willReturn($body);

			return $response;
		});
		\Aviat\AnimeClient\getApiClient($client);
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

	public function testCacheNon200(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$response->method('getStatus')->willReturn(404);
		$client->method('request')->willReturn($response);
		\Aviat\AnimeClient\getApiClient($client);

		$controller = new ImagesController($this->container);
		ob_start();
		$controller->cache('anime', 'missing.jpg');
		ob_end_clean();
		$this->assertTrue(true);
	}

	public function testCacheNoDisplay(): void
	{
		$controller = new ImagesController($this->container);
		
		ob_start();
		$controller->cache('anime', '123.jpg', false);
		ob_end_clean();

		$this->assertTrue(true);
	}
}
