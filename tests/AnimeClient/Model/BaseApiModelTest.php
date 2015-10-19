<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Container;
use Aviat\AnimeClient\Model\API as BaseApiModel;

class BaseApiModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->model = new MockBaseApiModel($this->container);
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
			'invalid method' => [
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
	
	public function dataAuthenticate()
	{
		$test_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI4YTA5ZDk4Ny1iZWQxLTQyMTktYWVmOS0wMTcxYWVjYTE3ZWUiLCJzY29wZSI6WyJhbGwiXSwic3ViIjoxMDgwMTIsImlzcyI6MTQ0NTAxNzczNSwiZXhwIjoxNDUyOTY2NTM1fQ.fpha1ZDN9dSFAuHeJesfOP9pCk5-ZnZk4uv3zumRMY0';
		
		return [
			'successful authentication' => [
				'username' => 'timw4mailtest',
				'password' => 'password',
				'response_data' => [
					'code' => 201,
					'body' => json_encode($test_token)
				],
				'expected' => $test_token
			],
			'failed authentication' => [
				'username' => 'foo',
				'password' => 'foobarbaz',
				'response_data' => [
					'code' => 401,
					'body' => '{"error":"Invalid credentials"}',
				],
				'expected' => FALSE
			]
		];
	}

	/**
	 * @dataProvider dataAuthenticate
	 */
	public function testAuthenticate($username, $password, $response_data, $expected)
	{
		$mock = new MockHandler([
			new Response($response_data['code'], [], $response_data['body'])
		]);
		$handler = HandlerStack::create($mock);
		$client = new Client([
			'handler' => $handler,
			'http_errors' => FALSE // Don't throw an exception for 400/500 class status codes
		]);
		
		// Set the mock client
		$this->model->__set('client', $client);
		
		// Check results based on mock data
		$actual = $this->model->authenticate($username, $password);
		$this->assertEquals($expected, $actual, "Incorrect method return value");
	}

}