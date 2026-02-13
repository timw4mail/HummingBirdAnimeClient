<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Anilist;

use Aviat\AnimeClient\API\Anilist\RequestBuilder;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class RequestBuilderTest extends AnimeClientTestCase
{
	protected RequestBuilder $builder;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->builder = new RequestBuilder($this->container);
	}

	public function testSetUpRequest(): void
	{
		$this->container->get('config')->set(['anilist', 'access_token'], 'token');
		$request = $this->builder->setUpRequest('https://example.com', [
			'body' => ['query' => 'test'],
			'headers' => ['X-Foo' => 'Bar'],
		]);

		$this->assertEquals('POST', $request->getMethod());
		$this->assertEquals('https://example.com', (string) $request->getUri());
		$this->assertTrue($request->hasHeader('Authorization'));
		$this->assertTrue($request->hasHeader('X-Foo'));
	}

	public function testRunQuery(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$response->method('getStatus')->willReturn(200);
		$client->method('request')->willReturn($response);
		\Aviat\AnimeClient\getApiClient($client);

		$result = $this->builder->runQuery('CheckLogin');
		$this->assertEquals(['data' => 'ok'], $result);
	}

	public function testMutate(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$response->method('getStatus')->willReturn(200);
		$client->method('request')->willReturn($response);
		\Aviat\AnimeClient\getApiClient($client);

		$result = $this->builder->mutate('UpdateMediaListEntry', ['id' => '1']);
		$this->assertEquals(['data' => 'ok'], $result);
	}
}
