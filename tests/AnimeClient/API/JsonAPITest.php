<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\JsonAPI;
use Aviat\Ion\Json;
use PHPUnit\Framework\TestCase;

class JsonAPITest extends TestCase {

	protected $startData;
	protected $organizedIncludes;
	protected $inlineIncluded;

	public function setUp(): void	{
		$dir = __DIR__ . '/../test_data/JsonAPI';
		$this->startData = Json::decodeFile("{$dir}/jsonApiExample.json");
		$this->organizedIncludes = Json::decodeFile("{$dir}/organizedIncludes.json");
		$this->inlineIncluded = Json::decodeFile("{$dir}/inlineIncluded.json");
	}

	public function testOrganizeIncludes()
	{
		$expected = $this->organizedIncludes;
		$actual = JsonAPI::organizeIncludes($this->startData['included']);

		// file_put_contents(__DIR__ . '/../test_data/JsonAPI/organizedIncludes.json', json_Encode($actual));

		$this->assertEquals($expected, $actual);
	}

	public function testInlineIncludedRelationships()
	{
		$expected = $this->inlineIncluded;
		$actual = JsonAPI::inlineIncludedRelationships($this->organizedIncludes, 'anime');

		// file_put_contents(__DIR__ . '/../test_data/JsonAPI/inlineIncluded.json', json_Encode($actual));

		$this->assertEquals($expected, $actual);
	}
}