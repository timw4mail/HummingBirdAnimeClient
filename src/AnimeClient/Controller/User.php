<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\API\Kitsu\Transformer\UserTransformer;
use Aviat\AnimeClient\Controller as BaseController;

use Aviat\Ion\Attribute\{Controller, Route};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};

/**
 * Controller for handling routes that don't fit elsewhere
 */
#[Controller]
final class User extends BaseController
{
	private Model $kitsuModel;

	/**
	 * User constructor.
	 *
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
	#[Route('default_user_info', '/me')]
	public function me(): void
	{
		$this->about('me');
	}

	/**
	 * Show the user profile page
	 */
	#[Route('user_info', '/user/{username}')]
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
		if ($rawData['data']['findProfileBySlug'] === NULL)
		{
			$this->notFound('Sorry, user not found', "The user '{$username}' does not seem to exist.");
		}

		$data = (new UserTransformer())->transform($rawData)->toArray();

		$this->outputHTML('user/details', [
			'title' => 'About ' . $whom,
			'data' => $data,
		]);
	}
}
