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

	public function testGetGenreList(): void
	{
		$friend = new Friend($this->model);
		$friend->db->set([
			'hummingbird_id' => 123,
			'slug' => 'test',
			'title' => 'Test',
		])->insert('anime_set');

		$friend->db->set([
			'id' => 1,
			'genre' => 'Action',
		])->insert('genres');

		$friend->db->set([
			'hummingbird_id' => 123,
			'genre_id' => 1,
		])->insert('anime_set_genre_link');

		$genres = $this->model->getGenreList();
		$this->assertArrayHasKey('123', $genres);
		$this->assertContains('Action', $genres['123']);
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

	public function testGetCollection(): void
	{
		$data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2], // Blu-ray
		];
		$this->model->add($data);

		$collection = $this->model->getCollection();
		$this->assertArrayHasKey('Blu-ray', $collection);
		$this->assertCount(1, $collection['Blu-ray']);
		$this->assertEquals('Test Anime 12345', $collection['Blu-ray'][0]['title']);
	}

	public function testAdd(): void
	{
		$data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2], // Blu-ray
		];

		$this->model->add($data);

		$this->assertTrue($this->model->has('12345'));
		$this->assertTrue($this->model->wasAdded($data));
		$item = $this->model->get('12345');
		$this->assertEquals('Test notes', $item['notes']);
		$this->assertEquals([2], $item['media_id']);
	}

	public function testUpdate(): void
	{
		$add_data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2],
		];
		$this->model->add($add_data);

		$update_data = [
			'hummingbird_id' => '12345',
			'notes' => 'Updated notes',
			'media_id' => [3], // DVD
		];

		$this->model->update($update_data);
		$this->assertTrue($this->model->wasUpdated($update_data));
		$item = $this->model->get('12345');
		$this->assertEquals('Updated notes', $item['notes']);
		$this->assertEquals([3], $item['media_id']);
	}

	public function testDelete(): void
	{
		$data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2],
		];
		$this->model->add($data);
		$this->assertTrue($this->model->has('12345'));

		$this->model->delete(['hummingbird_id' => '12345']);
		$this->assertFalse($this->model->has('12345'));
		$this->assertTrue($this->model->wasDeleted(['hummingbird_id' => '12345']));
	}

	public function testGetFlatCollection(): void
	{
		$data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2],
		];
		$this->model->add($data);

		$flat = $this->model->getFlatCollection();
		$this->assertCount(1, $flat);
		$this->assertEquals('Test Anime 12345', $flat[0]['title']);
		$this->assertContains('Action', $flat[0]['genres']);
	}

	public function testGetMediaList(): void
	{
		$data = [
			'id' => '12345',
			'notes' => 'Test notes',
			'media_id' => [2],
		];
		$this->model->add($data);

		$media = $this->model->getMediaList();
		$this->assertArrayHasKey('12345', $media);
		$this->assertContains('Blu-ray', $media['12345']);
	}
}
