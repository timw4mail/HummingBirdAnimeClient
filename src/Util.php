<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use abeautifulsite\SimpleImage;
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
	private static $form_pages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout',
		'details'
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
	public static function is_selected($a, $b)
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
	public static function is_not_selected($a, $b)
	{
		return ($a !== $b) ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @return bool
	 */
	public function is_view_page()
	{
		$url = $this->container->get('request')
			->getUri();
		$page_segments = explode("/", (string) $url);

		$intersect = array_intersect($page_segments, self::$form_pages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @return boolean
	 */
	public function is_form_page()
	{
		return ! $this->is_view_page();
	}
}

