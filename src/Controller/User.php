<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\API\Kitsu\Transformer\UserTransformer;
use Aviat\AnimeClient\Controller as BaseController;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * Controller for handling routes that don't fit elsewhere
 */
final class User extends BaseController {

	/**
	 * @var Model
	 */
	private $kitsuModel;

	/**
	 * User constructor.
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->kitsuModel = $container->get('kitsu-model');
	}

	/**
	 * Show the user profile page for the configured user
	 */
	public function me(): void
	{
		$this->about('me');
	}

	/**
	 * Show the user profile page
	 *
	 * @param string $username
	 * @return void
	 */
	public function about(string $username): void
	{
		$isMainUser = $username === 'me';

		$username = $isMainUser
			? $this->config->get(['kitsu_username'])
			: $username;

		$whom = $isMainUser
			? $this->config->get('whose_list')
			: $username;

		$rawData = $this->kitsuModel->getUserData($username);
		$data = (new UserTransformer())->transform($rawData)->toArray();

		$this->outputHTML('user/details', [
			'title' => 'About ' . $whom,
			'data' => $data,
		]);
	}
}