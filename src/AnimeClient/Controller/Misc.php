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
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Enum\EventType;
use Aviat\Ion\Event;
use Aviat\Ion\View\HtmlView;

/**
 * Controller for handling routes that don't fit elsewhere
 */
final class Misc extends BaseController {
	/**
	 * Purges the API cache
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->checkAuth();

		Event::emit(EventType::CLEAR_CACHE);

		$this->outputHTML('blank', [
			'title' => 'Cache cleared'
		]);
	}

	/**
	 * Show the login form
	 *
	 * @param string $status
	 * @return void
	 */
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
			'message' => $message
		], $view);
	}

	/**
	 * Attempt login authentication
	 *
	 * @return void
	 */
	public function loginAction(): void
	{
		$post = $this->request->getParsedBody();

		if ($this->auth->authenticate($post['password']))
		{
			$this->sessionRedirect();
			return;
		}

		$this->setFlashMessage('Invalid username or password.');
		$this->redirect($this->url->generate('login'), 303);
	}

	/**
	 * Deauthorize the current user
	 *
	 * @return void
	 */
	public function logout(): void
	{
		$this->auth->logout();

		$this->redirectToDefaultRoute();
	}

	/**
	 * Check if the current user is logged in
	 */
	public function heartbeat(): void
	{
		$this->outputJSON(['hasAuth' => $this->auth->isAuthenticated()], 200);
	}
}