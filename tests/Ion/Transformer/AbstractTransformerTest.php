<?php

class AbstractTransformerTest extends AnimeClient_TestCase {
	
	protected $transformer;


	public function setUp()
	{
		$this->transformer = new TestTransformer();
	}
	
	public function dataTransformCollection()
	{
		return [
			'object' => [
				'original' => [
					(object)[
						['name' => 'Comedy'],
						['name' => 'Romance'],
						['name' => 'School'],
						['name' => 'Harem']
					],
					(object)[
						['name' => 'Action'],
						['name' => 'Comedy'],
						['name' => 'Magic'],
						['name' => 'Fantasy'],
						['name' => 'Mahou Shoujo']
					],
					(object)[
						['name' => 'Comedy'],
						['name' => 'Sci-Fi']
					]
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			],
			'array' => [
				'original' => [
					[
						['name' => 'Comedy'],
						['name' => 'Romance'],
						['name' => 'School'],
						['name' => 'Harem']
					],
					[
						['name' => 'Action'],
						['name' => 'Comedy'],
						['name' => 'Magic'],
						['name' => 'Fantasy'],
						['name' => 'Mahou Shoujo']
					],
					[
						['name' => 'Comedy'],
						['name' => 'Sci-Fi']
					]
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			],
		];
	}
	
	public function testTransform()
	{
		$data = $this->dataTransformCollection();
		$original = $data['object']['original'][0];
		$expected = $data['object']['expected'][0];
		
		$actual = $this->transformer->transform($original);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider dataTransformCollection
	 */
	public function testTransformCollection($original, $expected)
	{
		$actual = $this->transformer->transform_collection($original);
		$this->assertEquals($expected, $actual);
	}
}