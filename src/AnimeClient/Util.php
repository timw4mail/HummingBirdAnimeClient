<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\{ContainerAware,  ContainerInterface};
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};

/**
 * Utility method class
 */
class Util {

	use ContainerAware;

	/**
	 * Routes that don't require a second navigation level
	 * @var array
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
		'me'
	];

	/**
	 * Set up the Util class
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Absolutely equal?
	 *
	 * @param $left
	 * @param $right
	 * @return bool
	 */
	public static function eq($left, $right): bool
	{
		return $left === $right;
	}

	/**
	 * Set aria-current attribute based on a condition check
	 *
	 * @param bool $condition
	 * @return string
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
	 * @return string
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
	 * @return string
	 */
	public static function isNotSelected(string $left, string $right): string
	{
		return ($left !== $right) ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return bool
	 */
	public function isViewPage(): bool
	{
		$url = $this->container->get('request')->getUri();
		$pageSegments = explode('/', (string) $url);

		$intersect = array_intersect($pageSegments, self::$formPages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @return bool
	 */
	public function isFormPage(): bool
	{
		return ! $this->isViewPage();
	}
}

