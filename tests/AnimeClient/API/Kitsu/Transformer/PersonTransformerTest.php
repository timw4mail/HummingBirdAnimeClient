<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\PersonTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\Person;

/**
 * @internal
 */
final class PersonTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new PersonTransformer();
		$data = [
			'data' => [
				'findPersonBySlug' => [
					'id' => '1',
					'slug' => 'test-person',
					'names' => [
						'canonical' => 'Canonical Name',
						'localized' => ['Canonical Name' => 'Actual Name'],
					],
					'image' => ['original' => ['url' => 'test.jpg']],
					'birthday' => '1990-01-01',
					'description' => ['en' => 'Test description'],
					'mediaStaff' => [
						'nodes' => [
							[
								'role' => 'Director',
								'media' => [
									'id' => '10',
									'type' => 'ANIME',
									'slug' => 'anime',
									'titles' => ['canonical' => 'Anime Title', 'localized' => []],
									'posterImage' => ['original' => ['url' => 'poster.jpg']],
								],
							],
							[
								'role' => 'Empty',
								'media' => [],
							],
						],
					],
					'voices' => [
						'nodes' => [
							[
								'mediaCharacter' => [
									'role' => 'MAIN',
									'character' => [
										'id' => '100',
										'slug' => 'char',
										'names' => ['canonical' => 'Char Name'],
										'image' => ['original' => ['url' => 'char.jpg']],
									],
									'media' => [
										'id' => '10',
										'slug' => 'anime',
										'titles' => ['canonical' => 'Anime Title'],
										'posterImage' => ['original' => ['url' => 'poster.jpg']],
									],
								],
							],
							null,
						],
					],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(Person::class, $result);
		$this->assertEquals('Actual Name', $result['name']);
		$this->assertArrayHasKey('Director', $result['staff']);
		$this->assertArrayHasKey('main', $result['characters']);
	}
}
