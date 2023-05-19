<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\APIRequestBuilder;
use Aviat\Ion\Json;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

/**
 * @internal
 */
final class APIRequestBuilderTest extends TestCase
{
	protected $builder;

	protected function setUp(): void
	{
		$this->builder = new class () extends APIRequestBuilder {
			protected string $baseUrl = 'https://httpbin.org/';
			protected array $defaultHeaders = ['User-Agent' => "Tim's Anime Client Testsuite / 4.0"];
		};

		$this->builder->setLogger(new NullLogger());
	}

	public function testGzipRequest(): void
	{
		$this->markTestSkipped('Need new test API');

		$request = $this->builder->newRequest('GET', 'gzip')
			->getFullRequest();
		$response = getResponse($request);
		$body = Json::decode(wait($response->getBody()->buffer()));
		$this->assertTrue($body['gzipped']);
	}

	public function testInvalidRequestMethod(): void
	{
		$this->markTestSkipped('Need new test API');

		$this->expectException(InvalidArgumentException::class);
		$this->builder->newRequest('FOO', 'gzip')
			->getFullRequest();
	}

	public function testRequestWithBasicAuth(): void
	{
		$this->markTestSkipped('Need new test API');

		$request = $this->builder->newRequest('GET', 'headers')
			->setBasicAuth('username', 'password')
			->getFullRequest();

		$response = getResponse($request);
		$body = Json::decode(wait($response->getBody()->buffer()));

		$this->assertSame('Basic dXNlcm5hbWU6cGFzc3dvcmQ=', $body['headers']['Authorization']);
	}

	public function testRequestWithQueryString(): void
	{
		$this->markTestSkipped('Need new test API');

		$query = [
			'foo' => 'bar',
			'bar' => [
				'foo' => 'bar',
			],
			'baz' => [
				'bar' => 'foo',
			],
		];

		$expected = [
			'bar[foo]' => 'bar',
			'baz[bar]' => 'foo',
			'foo' => 'bar',
		];

		$request = $this->builder->newRequest('GET', 'get')
			->setQuery($query)
			->getFullRequest();

		$response = getResponse($request);
		$body = Json::decode(wait($response->getBody()->buffer()));

		$this->assertSame($expected, $body['args']);
	}

	public function testFormValueRequest(): void
	{
		$this->markTestSkipped('Need new test API');

		$formValues = [
			'bar' => 'foo',
			'foo' => 'bar',
		];

		$request = $this->builder->newRequest('POST', 'post')
			->setFormFields($formValues)
			->getFullRequest();

		$response = getResponse($request);
		$body = Json::decode(wait($response->getBody()->buffer()));

		$this->assertSame($formValues, $body['form']);
	}

	public function testFullUrlRequest(): void
	{
		$this->markTestSkipped('Need new test API');

		$data = [
			'foo' => [
				'bar' => 1,
				'baz' => [2, 3, 4],
				'bazbar' => [
					'a' => 1,
					'b' => 2,
				],
			],
		];

		$request = $this->builder->newRequest('PUT', 'https://httpbin.org/put')
			->setHeader('Content-Type', 'application/json')
			->setJsonBody($data)
			->getFullRequest();

		$response = getResponse($request);
		$body = Json::decode(wait($response->getBody()->buffer()));

		$this->assertSame($data, $body['json']);
	}
}
