<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class ParallelAPIRequestTest extends AnimeClientTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$response->method('getStatus')->willReturn(200);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});
		\Aviat\AnimeClient\getApiClient($client);
	}

	public function testAddRequest(): void
	{
		$parallel = new ParallelAPIRequest();
		$parallel->addRequest('https://example.com', 'test');
		$friend = new \Aviat\Ion\Friend($parallel);
		$this->assertArrayHasKey('test', $friend->requests);
	}

	public function testAddRequests(): void
	{
		$parallel = new ParallelAPIRequest();
		$parallel->addRequests(['a' => 'url1', 'b' => 'url2']);
		$friend = new \Aviat\Ion\Friend($parallel);
		$this->assertArrayHasKey('a', $friend->requests);
		$this->assertArrayHasKey('b', $friend->requests);
	}

	public function testMakeRequests(): void
	{
		$parallel = new ParallelAPIRequest();
		$parallel->addRequest('https://example.com', 'test');
		$result = $parallel->makeRequests();
		$this->assertEquals(['test' => '{"data": "ok"}'], $result);
	}

	public function testGetResponses(): void
	{
		$parallel = new ParallelAPIRequest();
		$parallel->addRequest('https://example.com', 'test');
		$result = $parallel->getResponses();
		$this->assertArrayHasKey('test', $result);
		$this->assertInstanceOf(\Amp\Http\Client\Response::class, $result['test']);
	}

	public function testMakeRequestsWithObjects(): void
	{
		$parallel = new ParallelAPIRequest();
		$request = new \Amp\Http\Client\Request('https://example.com');
		$parallel->addRequest($request, 'test');
		$result = $parallel->makeRequests();
		$this->assertEquals(['test' => '{"data": "ok"}'], $result);
	}

	public function testGetResponsesWithObjects(): void
	{
		$parallel = new ParallelAPIRequest();
		$request = new \Amp\Http\Client\Request('https://example.com');
		$parallel->addRequest($request, 'test');
		$result = $parallel->getResponses();
		$this->assertArrayHasKey('test', $result);
		$this->assertInstanceOf(\Amp\Http\Client\Response::class, $result['test']);
	}
}
