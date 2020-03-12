<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Tests;

use function Aviat\Ion\_dir;

use Aviat\Ion\{Json, JsonException};

class JsonTest extends Ion_TestCase {

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
		$this->expectException('Aviat\Ion\JsonException');
		$this->expectExceptionMessage('JSON_ERROR_SYNTAX - Syntax error');
		$this->expectExceptionCode(JSON_ERROR_SYNTAX);

		Json::decode($badJson);
	}

	public function testDecodeNull()
	{
		$this->assertNull(Json::decode(NULL));
	}
}