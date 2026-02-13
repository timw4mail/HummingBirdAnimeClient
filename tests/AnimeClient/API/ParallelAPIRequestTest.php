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
	public function testParallelRequest(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);

		$parallel = new ParallelAPIRequest();
		$parallel->addRequest('https://example.com/1', 'first');
		$parallel->addRequest('https://example.com/2', 'second');

		$results = $parallel->makeRequests();

		$this->assertCount(2, $results);
		$this->assertEquals('{"data": "ok"}', $results['first']);
		$this->assertEquals('{"data": "ok"}', $results['second']);
	}

	public function testGetResponses(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				return $this->createMock(\Amp\Http\Client\Response::class);
			});

		\Aviat\AnimeClient\getApiClient($client);

		$parallel = new ParallelAPIRequest();
		$parallel->addRequests([
			'https://example.com/1',
			'https://example.com/2',
		]);

		$responses = $parallel->getResponses();

		$this->assertCount(2, $responses);
		$this->assertInstanceOf(\Amp\Http\Client\Response::class, $responses[0]);
	}

	public function testAddRequests(): void
	{
		$parallel = new ParallelAPIRequest();
		$parallel->addRequests(['a', 'b']);

		$friend = new \Aviat\Ion\Friend($parallel);
		$this->assertCount(2, $friend->requests);
	}
}
