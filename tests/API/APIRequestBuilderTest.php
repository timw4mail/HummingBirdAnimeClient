<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use function Amp\Promise\wait;
use Aviat\AnimeClient\API\{APIRequestBuilder, HummingbirdClient};
use Aviat\Ion\Json;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class APIRequestBuilderTest extends TestCase {

	protected $builder;

	public function setUp()
	{
		$this->builder = new class extends APIRequestBuilder {
			protected $baseUrl = 'https://httpbin.org/';

			protected $defaultHeaders = ['User-Agent' => "Tim's Anime Client Testsuite / 4.0"];
		};

		$this->builder->setLogger(new NullLogger);
	}

	public function testGzipRequest()
	{
		$request = $this->builder->newRequest('GET', 'gzip')
			->getFullRequest();
		$response = wait((new HummingbirdClient)->request($request));
		$body = Json::decode(wait($response->getBody()));
		$this->assertEquals(1, $body['gzipped']);
	}

	public function testInvalidRequestMethod()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->builder->newRequest('FOO', 'gzip')
			->getFullRequest();
	}

	public function testRequestWithBasicAuth()
	{
		$request = $this->builder->newRequest('GET', 'headers')
			->setBasicAuth('username', 'password')
			->getFullRequest();

		$response = wait((new HummingbirdClient)->request($request));
		$body = Json::decode(wait($response->getBody()));

		$this->assertEquals('Basic dXNlcm5hbWU6cGFzc3dvcmQ=', $body['headers']['Authorization']);
	}

	public function testRequestWithQueryString()
	{
		$query = [
			'foo' => 'bar',
			'bar' => [
				'foo' => 'bar'
			],
			'baz' => [
				'bar' => 'foo'
			]
		];

		$expected = [
			'foo' => 'bar',
			'bar[foo]' => 'bar',
			'baz[bar]' => 'foo'
		];

		$request = $this->builder->newRequest('GET', 'get')
			->setQuery($query)
			->getFullRequest();

		$response = wait((new HummingbirdClient)->request($request));
		$body = Json::decode(wait($response->getBody()));

		$this->assertEquals($expected, $body['args']);
	}

	public function testFormValueRequest()
	{
		$formValues = [
			'foo' => 'bar',
			'bar' => 'foo'
		];

		$request = $this->builder->newRequest('POST', 'post')
			->setFormFields($formValues)
			->getFullRequest();

		$response = wait((new HummingbirdClient)->request($request));
		$body = Json::decode(wait($response->getBody()));

		$this->assertEquals($formValues, $body['form']);
	}

	public function testFullUrlRequest()
	{
		$data = [
			'foo' => [
				'bar' => 1,
				'baz' => [2, 3, 4],
				'bazbar' => [
					'a' => 1,
					'b' => 2
				]
			]
		];

		$request = $this->builder->newRequest('PUT', 'https://httpbin.org/put')
			->setHeader('Content-Type', 'application/json')
			->setJsonBody($data)
			->getFullRequest();

		$response = wait((new HummingbirdClient)->request($request));
		$body = Json::decode(wait($response->getBody()));

		$this->assertEquals($data, $body['json']);
	}
}