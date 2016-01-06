<?php

use Aviat\Ion\Json;
use Aviat\Ion\JsonException;

class JsonTest extends AnimeClient_TestCase {

	public function testEncode()
	{
		$data = (object) [
			'foo' => [1, 2, 3, 4]
		];
		$expected = '{"foo":[1,2,3,4]}';
		$this->assertEquals($expected, Json::encode($data));
	}

	public function dataEncodeDecode()
	{
		return [
			'set1' => [
				'data' => [
					'apple' => [
						'sauce' => ['foo','bar','baz']
					]
				],
				'expected_size' => 39,
				'expected_json' => '{"apple":{"sauce":["foo","bar","baz"]}}'
			]
		];
	}

	/**
	 * @dataProvider dataEncodeDecode
	 */
	public function testEncodeDecodeFile($data, $expected_size, $expected_json)
	{
		$target_file = _dir(self::TEST_DATA_DIR, 'json_write.json');

		$actual_size = Json::encodeFile($target_file, $data);
		$actual_json = file_get_contents($target_file);

		$this->assertTrue(Json::isJson($actual_json));
		$this->assertEquals($expected_size, $actual_size);
		$this->assertEquals($expected_json, $actual_json);

		$this->assertEquals($data, Json::decodeFile($target_file));

		unlink($target_file);
	}

	public function testDecode()
	{
		$json = '{"foo":[1,2,3,4]}';
		$expected = [
			'foo' => [1, 2, 3, 4]
		];
		$this->assertEquals($expected, Json::decode($json));
		$this->assertEquals((object)$expected, Json::decode($json, false));

		$badJson = '{foo:{1|2}}';
		$this->setExpectedException(
			'Aviat\Ion\JsonException',
			'JSON_ERROR_SYNTAX - Syntax error',
			JSON_ERROR_SYNTAX
		);
		Json::decode($badJson);
	}
}