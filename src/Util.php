<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};

/**
 * Utility method class
 */
class Util {

	use ContainerAware;

	/**
	 * Routes that don't require a second navigation level
	 * @var array
	 */
	private static $formPages = [
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * HTML selection helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function isSelected(string $a, string $b): string
	{
		return ($a === $b) ? 'selected' : '';
	}

	/**
	 * Inverse of selected helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function isNotSelected(string $a, string $b): string
	{
		return ($a !== $b) ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
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
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 * @return bool
	 */
	public function isFormPage(): bool
	{
		return ! $this->isViewPage();
	}
}

