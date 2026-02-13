<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Model\AnimeCollection;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\Anime as AnimeType;
use Aviat\Ion\Friend;

class MockAnimeModel extends AnimeModel
{
	public function __construct() {}

	#[\Override]
	public function getAnimeById(string $id): AnimeType
	{
		return AnimeType::from([
			'slug' => 'test-anime-' . $id,
			'titles' => ['Test Anime ' . $id, 'Alternate Title'],
			'show_type' => 'TV',
			'age_rating' => 'PG13',
			'cover_image' => 'test.jpg',
			'episode_count' => 12,
			'episode_length' => 24,
			'genres' => ['Action', 'Adventure'],
		]);
	}
}

final class AnimeCollectionTest extends AnimeClientTestCase
{
	protected AnimeCollection $model;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		// Mock the anime model
		$this->container->set('anime-model', fn () => new MockAnimeModel());

		$this->model = $this->container->get('anime-collection-model');
	}

	public function testGetCollectionEmpty(): void
	{
		$this->assertEquals([], $this->model->getCollection());
	}

	public function testGetFlatCollectionEmpty(): void
	{
		$this->assertEquals([], $this->model->getFlatCollection());
	}

	public function testGetMediaTypeList(): void
	{
		$types = $this->model->getMediaTypeList();
		$this->assertArrayHasKey('Common', $types);
		$this->assertArrayHasKey('Retro', $types);
		$this->assertArrayHasKey('Other', $types);
	}

	public function testHas(): void
	{
		$friend = new Friend($this->model);
		$friend->db->set([
			'hummingbird_id' => 999,
			'slug' => 'test',
			'title' => 'Test',
		])->insert('anime_set');

		$this->assertTrue($this->model->has(999));
	}
}
