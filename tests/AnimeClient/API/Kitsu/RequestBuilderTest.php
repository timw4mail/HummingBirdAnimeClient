<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\RequestBuilder;
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

	public function testQueryRequest(): void
	{
		// Use a query that we know exists, e.g., GetUserId
		$request = $this->builder->queryRequest('GetUserId', ['slug' => 'test']);
		$this->assertEquals('POST', $request->getMethod());
		$body = json_decode($request->getBody()->getContent()->read(), true);
		$this->assertArrayHasKey('query', $body);
		$this->assertEquals('test', $body['variables']['slug']);
	}

	public function testMutateRequest(): void
	{
		$request = $this->builder->mutateRequest('CreateLibraryItem', [
			'id' => '1',
			'status' => 'CURRENT',
			'type' => 'ANIME',
			'userId' => '1',
		]);
		$this->assertEquals('POST', $request->getMethod());
		$body = json_decode($request->getBody()->getContent()->read(), true);
		$this->assertArrayHasKey('query', $body);
		$this->assertStringContainsString('mutation', $body['query']);
	}

	public function testSetUpRequest(): void
	{
		$this->container->get('cache')->set(\Aviat\AnimeClient\Kitsu::AUTH_TOKEN_CACHE_KEY, 'test-token');
		$request = $this->builder->setUpRequest('GET', 'https://example.com');
		$this->assertEquals('Bearer test-token', $request->getHeader('Authorization'));
	}

	public function testSetUpRequestFromSession(): void
	{
		$this->container->get('cache')->clear();
		$this->container->get('session')
			->getSegment(\Aviat\AnimeClient\SESSION_SEGMENT)
			->set('auth_token', 'session-token');
		
		$request = $this->builder->setUpRequest('GET', 'https://example.com');
		$this->assertEquals('Bearer session-token', $request->getHeader('Authorization'));
		$this->assertEquals('session-token', $this->container->get('cache')->get(\Aviat\AnimeClient\Kitsu::AUTH_TOKEN_CACHE_KEY));
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

		$result = $this->builder->runQuery('GetUserId', ['slug' => 'test']);
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

		$result = $this->builder->mutate('CreateLibraryItem', ['id' => '1']);
		$this->assertEquals(['data' => 'ok'], $result);
	}

	public function testGetResponse(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$response->method('getBody')->willReturn(new \Amp\ByteStream\Payload(''));
		$client->method('request')->willReturn($response);
		\Aviat\AnimeClient\getApiClient($client);

		$result = $this->builder->getResponse('GET', 'https://example.com');
		$this->assertSame($response, $result);
	}
}
