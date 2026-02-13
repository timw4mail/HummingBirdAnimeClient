<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\UserTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\User;

/**
 * @internal
 */
final class UserTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new UserTransformer();
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'about' => 'About',
					'avatarImage' => ['original' => ['url' => 'avatar.jpg']],
					'birthday' => '1990-01-01',
					'createdAt' => '2015-01-01',
					'gender' => 'Male',
					'location' => 'Location',
					'name' => 'Name',
					'slug' => 'slug',
					'waifu' => ['id' => '1', 'names' => ['canonical' => 'Waifu']],
					'waifuOrHusbando' => 'Waifu',
					'siteLinks' => ['nodes' => [['url' => 'https://example.com']]],
					'favorites' => [
						'nodes' => [
							[
								'id' => '1',
								'item' => [
									'__typename' => 'Anime',
									'id' => '10',
									'titles' => ['canonical' => 'Fav Anime'],
								],
							],
						],
					],
					'stats' => [
						'animeAmountConsumed' => [
							'time' => 1000,
							'media' => 10,
							'units' => 100,
						],
						'mangaAmountConsumed' => [
							'time' => 500,
							'media' => 5,
							'units' => 50,
						],
					],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(User::class, $result);
		$this->assertEquals('Name', $result['name']);
		$this->assertArrayHasKey('anime', $result['favorites']);
		$this->assertArrayHasKey('Time spent watching anime:', $result['stats']);
		$this->assertArrayHasKey('Manga series read:', $result['stats']);
		$this->assertEquals('https://example.com', $result['website']);
	}

	public function testTransformMinimal(): void
	{
		$transformer = new UserTransformer();
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'about' => null,
					'avatarImage' => null,
					'birthday' => null,
					'createdAt' => '2015-01-01',
					'gender' => null,
					'location' => null,
					'name' => 'Name',
					'slug' => 'slug',
					'siteLinks' => ['nodes' => []],
					'favorites' => ['nodes' => []],
					'stats' => [],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(User::class, $result);
		$this->assertNull($result['birthday']);
		$this->assertEquals('', $result['about']);
	}
}
