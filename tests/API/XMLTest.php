<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\XML;
use PHPUnit\Framework\TestCase;

class XMLTest extends TestCase {

	public function setUp()
	{
		$this->malExport = file_get_contents(__DIR__ . '/../test_data/XML/MALExport.xml');
		$this->xml = file_get_contents(__DIR__ . '/../test_data/XML/xmlTestFile.xml');
		$this->expectedXml = file_get_contents(__DIR__ . '/../test_data/XML/minifiedXmlTestFile.xml');

		$this->array = [
			'entry' => [
				'foo' => [
					'bar' => [
						'baz' => 42
					]
				],
				'episode' => '11',
				'status' => 'watching',
				'score' => '7',
				'storage_type' => '1',
				'storage_value' => '2.5',
				'times_rewatched' => '1',
				'rewatch_value' => '3',
				'date_start' => '01152015',
				'date_finish' => '10232016',
				'priority' => '2',
				'enable_discussion' => '0',
				'enable_rewatching' => '1',
				'comments' => 'Should you say something?',
				'tags' => 'test tag, 2nd tag'
			]
		];

		$this->object = new XML();
	}

	public function testToArray()
	{
		$this->assertEquals($this->array, XML::toArray($this->xml));
	}
	
	public function testMALExport()
	{
		$array = XML::toArray($this->malExport);
		$this->assertEquals($array['myanimelist']['myinfo']['user_total_anime'], count($array['myanimelist']['anime']));
		// $this->assertEquals($array, XML::toArray($this->malExport));
	}

	public function testParse()
	{
		$this->object->setXML($this->xml);
		$this->assertEquals($this->array, $this->object->parse());
	}

	public function testToXML()
	{
		$this->assertEquals($this->expectedXml, XML::toXML($this->array));
	}

	public function testCreateXML()
	{
		$this->object->setData($this->array);
		$this->assertEquals($this->expectedXml, $this->object->createXML());
	}

	public function testToString()
	{
		$this->object->setData($this->array);
		$this->assertEquals($this->expectedXml, $this->object->__toString());
		$this->assertEquals($this->expectedXml, (string) $this->object);
	}
}