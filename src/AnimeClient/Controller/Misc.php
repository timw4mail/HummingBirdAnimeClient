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
use Aviat\AnimeClient\API\Kitsu\Transformer\{CharacterTransformer, PersonTransformer};
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Enum\EventType;
use Aviat\Ion\Attribute\{DefaultController, Route};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Event;
use Aviat\Ion\View\HtmlView;

/**
 * Controller for handling routes that don't fit elsewhere
 */
#[DefaultController]
final class Misc extends BaseController
{
	private Model $model;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->model = $container->get('kitsu-model');
	}

	/**
	 * Redirect to the default controller/url from an empty path
	 */
	#[Route('index_redirect', '/')]
	public function index(): void
	{
		parent::redirectToDefaultRoute();
	}

	/**
	 * Purges the API cache
	 */
	#[Route('cache_purge', '/cache_purge')]
	public function clearCache(): void
	{
		$this->checkAuth();

		Event::emit(EventType::CLEAR_CACHE);

		$this->outputHTML('blank', [
			'title' => 'Cache cleared',
		]);
	}

	/**
	 * Show the login form
	 */
	#[Route('login', '/login')]
	public function login(string $status = ''): void
	{
		$message = '';

		$view = new HtmlView($this->container);

		if ($status !== '')
		{
			$message = $this->showMessage($view, 'error', $status);
		}

		// Set the redirect url
		$this->setSessionRedirect();

		$this->outputHTML('login', [
			'title' => 'Api login',
			'message' => $message,
		], $view);
	}

	/**
	 * Attempt login authentication
	 */
	#[Route('login.post', '/login', Route::POST)]
	public function loginAction(): void
	{
		$post = (array) $this->request->getParsedBody();

		if ($this->auth->authenticate($post['password']))
		{
			$this->sessionRedirect();

			return;
		}

		$this->setFlashMessage('Invalid username or password.');

		$redirectUrl = $this->url->generate('login');
		$redirectUrl = ($redirectUrl !== FALSE) ? $redirectUrl : '';

		$this->redirect($redirectUrl, 303);
	}

	/**
	 * Deauthorize the current user
	 */
	#[Route('logout', '/logout')]
	public function logout(): void
	{
		$this->auth->logout();

		$this->redirectToDefaultRoute();
	}

	/**
	 * Check if the current user is logged in
	 */
	#[Route('heartbeat', '/heartbeat')]
	public function heartbeat(): void
	{
		$this->outputJSON(['hasAuth' => $this->auth->isAuthenticated()], 200);
	}

	/**
	 * Show information about a character
	 */
	#[Route('character', '/character/{slug}')]
	public function character(string $slug): void
	{
		$rawData = $this->model->getCharacter($slug);

		if (( ! array_key_exists('data', $rawData)) || empty($rawData['data']))
		{
			$this->notFound(
				$this->formatTitle(
					'Characters',
					'Character not found'
				),
				'Character Not Found'
			);

			return;
		}

		$data = (new CharacterTransformer())->transform($rawData)->toArray();

		$this->outputHTML('character/details', [
			'title' => $this->formatTitle(
				'Characters',
				$data['name']
			),
			'data' => $data,
		]);
	}

	/**
	 * Show information about a person
	 */
	#[Route('person', '/people/{slug}')]
	public function person(string $slug): void
	{
		$rawData = $this->model->getPerson($slug);
		$data = (new PersonTransformer())->transform($rawData)->toArray();

		if (( ! array_key_exists('data', $rawData)) || empty($rawData['data']))
		{
			$this->notFound(
				$this->formatTitle(
					'People',
					'Person not found'
				),
				'Person Not Found'
			);

			return;
		}

		$this->outputHTML('person/details', [
			'title' => $this->formatTitle(
				'People',
				$data['name']
			),
			'data' => $data,
		]);
	}
}
