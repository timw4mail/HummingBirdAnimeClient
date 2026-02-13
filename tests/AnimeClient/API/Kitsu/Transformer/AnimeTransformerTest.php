<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\AnimePage;

final class AnimeTransformerTest extends AnimeClientTestCase
{
	protected AnimeTransformer $transformer;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->transformer = new AnimeTransformer();
	}

	public function testTransform(): void
	{
		$data = $this->getMockFileData('Kitsu', 'animeBeforeTransform.json');
		$result = $this->transformer->transform($data);

		$this->assertInstanceOf(AnimePage::class, $result);
		$this->assertMatchesSnapshot($result);
	}
}
