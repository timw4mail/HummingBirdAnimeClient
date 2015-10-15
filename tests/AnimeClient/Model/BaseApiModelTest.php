<?php

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Container;
use Aviat\AnimeClient\Model\API as BaseApiModel;

class MockBaseApiModel extends BaseApiModel {

	protected $base_url = 'https://httpbin.org/';

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
	}

	public function __get($key)
	{
		return $this->$key;
	}
}

class BaseApiModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->model = new MockBaseApiModel($this->container);
	}

	public function testBaseApiModelSanity()
	{
		$baseApiModel = $this->model;

		// Some basic type checks for class memebers
		$this->assertInstanceOf('\Aviat\AnimeClient\Model', $baseApiModel);
		$this->assertInstanceOf('\Aviat\AnimeClient\Model\API', $baseApiModel);

		$this->assertInstanceOf('\GuzzleHttp\Client', $baseApiModel->client);
		$this->assertInstanceOf('\GuzzleHttp\Cookie\CookieJar', $baseApiModel->cookieJar);

		$this->assertTrue(is_string($baseApiModel->base_url));
		$this->assertTrue(empty($baseApiModel->base_url));
	}

	protected function getIp()
	{
		$response = $this->model->get('/ip');
		$json = json_decode($response->getBody(), TRUE);
		$ip = $json['origin'];
		return $ip;
	}

	public function dataClient()
	{
		$user_agent = "Tim's Anime Client/2.0";
		$headers = [
			'User-Agent' => $user_agent
		];

		return [
			'invalid' => [
				'method' => 'foo',
				'uri' => '',
				'options' => [],
				'expected' => NULL,
				'is_json' => FALSE,
			],
			'get' => [
				'method' => 'get',
				'uri' => '/get',
				'options' => [
					'query' => [
						'foo' => 'bar'
					],
					'headers' => $headers
				],
				'expected' => [
					'args' => [
						'foo' => 'bar'
					],
					'headers' => [
						'Host' => 'httpbin.org',
						'User-Agent' => $user_agent
					],
					'url' => 'https://httpbin.org/get?foo=bar'
				],
				'is_json' => TRUE
			],
			'post' => [
				'method' => 'post',
				'uri' => '/post',
				'options' => [
					'form_params' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => $headers
				],
				'expected' => [
					'args' => [],
					'data' => '',
					'files' => [],
					'form' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => [
						'Host' => 'httpbin.org',
						'User-Agent' => $user_agent,
						'Content-Length' => '18',
						'Content-Type' => 'application/x-www-form-urlencoded'
					],
					'json' => NULL,
					'url' => 'https://httpbin.org/post'
				],
				'is_json' => TRUE
			],
			'put' => [
				'method' => 'put',
				'uri' => '/put',
				'options' => [
					'form_params' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => $headers
				],
				'expected' => [
					'args' => [],
					'data' => '',
					'files' => [],
					'form' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => [
						'Host' => 'httpbin.org',
						'User-Agent' => $user_agent,
						'Content-Length' => '18',
						'Content-Type' => 'application/x-www-form-urlencoded'
					],
					'json' => NULL,
					'url' => 'https://httpbin.org/put'
				],
				'is_json' => TRUE
			],
			'patch' => [
				'method' => 'patch',
				'uri' => '/patch',
				'options' => [
					'form_params' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => $headers
				],
				'expected' => [
					'args' => [],
					'data' => '',
					'files' => [],
					'form' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => [
						'Host' => 'httpbin.org',
						'User-Agent' => $user_agent,
						'Content-Length' => '18',
						'Content-Type' => 'application/x-www-form-urlencoded'
					],
					'json' => NULL,
					'url' => 'https://httpbin.org/patch'
				],
				'is_json' => TRUE
			],
			'delete' => [
				'method' => 'delete',
				'uri' => '/delete',
				'options' => [
					'form_params' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => $headers
				],
				'expected' => [
					'args' => [],
					'data' => '',
					'files' => [],
					'form' => [
						'foo' => 'bar',
						'baz' => 'foobar'
					],
					'headers' => [
						'Host' => 'httpbin.org',
						'User-Agent' => $user_agent,
						'Content-Length' => '18',
						'Content-Type' => 'application/x-www-form-urlencoded'
					],
					'json' => NULL,
					'url' => 'https://httpbin.org/delete'
				],
				'is_json' => TRUE
			]
		];
	}

	/**
	 * @dataProvider dataClient
	 */
	public function testClient($method, $uri, $options, $expected, $is_json)
	{

		$result = $this->model->$method($uri, $options);

		if (is_null($result))
		{
			$this->assertNull($expected);
			return;
		}

		// Because you have to make another api call to get the origin ip
		// address, it needs to be retreived outside of the dataProvider method
		$expected['origin'] = $this->getIp();
		$actual = ($is_json)
			? json_decode($result->getBody(), TRUE)
			: (string) $result->getBody();

		$this->assertEquals($expected, $actual);
	}

}