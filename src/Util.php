<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use DomainException;

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
	 * The config manager
	 * @var ConfigInterface
	 */
	private $config;

	/**
	 * Set up the Util class
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->config = $container->get('config');
	}

	/**
	 * HTML selection helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function isSelected($a, $b)
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
	public static function isNotSelected($a, $b)
	{
		return ($a !== $b) ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @return bool
	 */
	public function isViewPage()
	{
		$url = $this->container->get('request')->getUri();
		$pageSegments = explode("/", (string) $url);

		$intersect = array_intersect($pageSegments, self::$formPages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @return boolean
	 */
	public function isFormPage()
	{
		return ! $this->isViewPage();
	}
}

