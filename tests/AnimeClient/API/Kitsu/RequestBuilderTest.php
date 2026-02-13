<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\RequestBuilder;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

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
}
