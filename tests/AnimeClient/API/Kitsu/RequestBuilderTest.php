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
}
