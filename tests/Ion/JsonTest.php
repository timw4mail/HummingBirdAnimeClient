<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\{Json, JsonException};

use function Aviat\Ion\_dir;

/**
 * @internal
 */
final class JsonTest extends IonTestCase
{
	public function testEncode()
	{
		$data = (object) [
			'foo' => [1, 2, 3, 4],
		];
		$expected = '{"foo":[1,2,3,4]}';
		$this->assertSame($expected, Json::encode($data));
	}

	public static function dataEncodeDecode(): array
	{
		return [
			'set1' => [
				'data' => [
					'apple' => [
						'sauce' => ['foo', 'bar', 'baz'],
					],
				],
				'expected_size' => 39,
				'expected_json' => '{"apple":{"sauce":["foo","bar","baz"]}}',
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('dataEncodeDecode')]
 public function testEncodeDecodeFile(array $data, int $expected_size, string $expected_json): void
 {
 	$target_file = _dir(self::TEST_DATA_DIR, 'json_write.json');

 	$actual_size = Json::encodeFile($target_file, $data);
 	$actual_json = file_get_contents($target_file);

 	$this->assertTrue(Json::isJson($actual_json));
 	$this->assertSame($expected_size, $actual_size);
 	$this->assertSame($expected_json, $actual_json);

 	$this->assertEquals($data, Json::decodeFile($target_file));

 	unlink($target_file);
 }

	public function testDecode()
	{
		$json = '{"foo":[1,2,3,4]}';
		$expected = [
			'foo' => [1, 2, 3, 4],
		];
		$this->assertSame($expected, Json::decode($json));
		$this->assertEquals((object) $expected, Json::decode($json, FALSE));

		$badJson = '{foo:{1|2}}';
		$this->expectException(\Aviat\Ion\JsonException::class);
		$this->expectExceptionMessage('JSON_ERROR_SYNTAX - Syntax error');
		$this->expectExceptionCode(JSON_ERROR_SYNTAX);

		Json::decode($badJson);
	}

	public function testDecodeNull()
	{
		$this->assertNull(Json::decode(NULL));
	}
}
