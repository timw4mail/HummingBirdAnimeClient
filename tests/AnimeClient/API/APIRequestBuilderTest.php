<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\APIRequestBuilder;
use PHPUnit\Framework\TestCase;

class APIRequestBuilderTest extends TestCase
{
	protected $builder;

	protected function setUp(): void
	{
		$this->builder = new class extends APIRequestBuilder {};
	}

	public function testSimpleRequest(): void
	{
		$request = APIRequestBuilder::simpleRequest('https://example.com');
		$this->assertEquals('https://example.com', (string) $request->getUri());
		$this->assertArrayHasKey('user-agent', $request->getHeaders());
	}

	public function testNewRequest(): void
	{
		$this->builder->newRequest('POST', 'https://example.com/api');
		$request = $this->builder->getFullRequest();
		$this->assertEquals('POST', $request->getMethod());
		$this->assertEquals('https://example.com/api', (string) $request->getUri());
	}

	public function testSetAuth(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setAuth('bearer', 'token123');
		$request = $this->builder->getFullRequest();
		$this->assertEquals('Bearer token123', $request->getHeader('Authorization'));
	}

	public function testSetBasicAuth(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setBasicAuth('user', 'pass');
		$request = $this->builder->getFullRequest();
		$expected = 'Basic ' . base64_encode('user:pass');
		$this->assertEquals($expected, $request->getHeader('Authorization'));
	}

	public function testSetHeaders(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setHeaders([
			'X-Test' => 'Foo',
			'X-Another' => 'Bar',
		]);
		$request = $this->builder->getFullRequest();
		$this->assertEquals('Foo', $request->getHeader('X-Test'));
		$this->assertEquals('Bar', $request->getHeader('X-Another'));
	}

	public function testSetQuery(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setQuery(['foo' => 'bar', 'baz' => 'qux']);
		$request = $this->builder->getFullRequest();
		$this->assertEquals('https://example.com?foo=bar&baz=qux', (string) $request->getUri());
	}

	public function testSetFormFields(): void
	{
		$this->builder->newRequest('POST', 'https://example.com');
		$this->builder->setFormFields(['foo' => 'bar']);
		$request = $this->builder->getFullRequest();
		$this->assertInstanceOf(\Amp\Http\Client\Form::class, $request->getBody());
	}

	public function testSetJsonBody(): void
	{
		$this->builder->newRequest('POST', 'https://example.com');
		$this->builder->setJsonBody(['foo' => 'bar']);
		$request = $this->builder->getFullRequest();
		$this->assertEquals('{"foo":"bar"}', $request->getBody()->getContent()->read());
	}

	public function testUnsetHeader(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setHeader('X-Test', 'Foo');
		$this->assertEquals('Foo', $this->builder->getFullRequest()->getHeader('X-Test'));
		
		$this->builder->unsetHeader('X-Test');
		$this->assertNull($this->builder->getFullRequest()->getHeader('X-Test'));
	}

	public function testSetHeaderNull(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$this->builder->setHeader('X-Test', 'Foo');
		$this->assertEquals('Foo', $this->builder->getFullRequest()->getHeader('X-Test'));
		
		$this->builder->setHeader('X-Test', null);
		$this->assertNull($this->builder->getFullRequest()->getHeader('X-Test'));
	}

	public function testSetHeaderEmptyName(): void
	{
		$this->builder->newRequest('GET', 'https://example.com');
		$ret = $this->builder->setHeader('', 'Foo');
		$this->assertSame($this->builder, $ret);
	}
}
