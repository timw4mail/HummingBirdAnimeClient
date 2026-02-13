<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;

/**
 * @internal
 */
final class APITest extends AnimeClientTestCase
{
	public function testSortByName(): void
	{
		$model = new class extends \Aviat\AnimeClient\Model\API {
			public function doSort(&$data, $key)
			{
				$this->sortByName($data, $key);
			}
		};

		$data = [
			[
				'id' => '1',
				'anime' => ['title' => 'B'],
			],
			[
				'id' => '2',
				'anime' => ['title' => 'A'],
			],
		];

		$model->doSort($data, 'anime');

		$keys = array_keys($data);
		$this->assertEquals(['2', '1'], $keys);
		$this->assertEquals('A', $data['2']['anime']['title']);
	}
}
