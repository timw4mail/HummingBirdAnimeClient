<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
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
	 */
	public function loginAction(): void
	{
		$post = (array)$this->request->getParsedBody();

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