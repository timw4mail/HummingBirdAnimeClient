<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Utility method class
 */
class Util
{
	use ContainerAware;

	/**
	 * Routes that don't require a second navigation level
	 * @var list<string>
	 */
	private static array $formPages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout',
		'details',
		'character',
		'me',
	];

	/**
	 * Set up the Util class
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Absolutely equal?
	 */
	public static function eq(mixed $left, mixed $right): bool
	{
		return $left === $right;
	}

	/**
	 * Set aria-current attribute based on a condition check
	 */
	public static function ariaCurrent(bool $condition): string
	{
		return $condition ? 'true' : 'false';
	}

	/**
	 * HTML selection helper function
	 *
	 * @param string $left - First item to compare
	 * @param string $right - Second item to compare
	 */
	public static function isSelected(string $left, string $right): string
	{
		return static::eq($left, $right) ? 'selected' : '';
	}

	/**
	 * Inverse of selected helper function
	 *
	 * @param string $left - First item to compare
	 * @param string $right - Second item to compare
	 */
	public static function isNotSelected(string $left, string $right): string
	{
		return $left !== $right ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function isViewPage(): bool
	{
		$url = $this->container->get('request')->getUri();
		$pageSegments = explode('/', (string) $url);

		$intersect = array_intersect($pageSegments, self::$formPages);

		return $intersect === [];
	}
}
