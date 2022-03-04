<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\Di;

use Aviat\Ion\Di\{Container, ContainerAware, ContainerInterface};
use Aviat\Ion\Tests\IonTestCase;

class Aware
{
	use ContainerAware;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
}

/**
 * @internal
 */
final class ContainerAwareTest extends IonTestCase
{
	protected Aware $aware;

	protected function setUp(): void
	{
		$this->container = new Container();
		$this->aware = new Aware($this->container);
	}

	public function testContainerAwareTrait(): void
	{
		// The container was set in setup
		// check that the get method returns the same
		$this->assertSame($this->container, $this->aware->getContainer());

		$container2 = new Container([
			'foo' => 'bar',
			'baz' => 'foobar',
		]);
		$this->aware->setContainer($container2);
		$this->assertSame($container2, $this->aware->getContainer());
	}
}
