<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aura\Router\Exception\RouteNotFound;
use Aviat\AnimeClient\API\Anilist\Model as AnilistModel;
use Aviat\AnimeClient\Controller as BaseController;
use Aviat\AnimeClient\Model\Settings as SettingsModel;
use Aviat\Ion\Attribute\{Controller, Route};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};

/**
 * Controller for user settings
 */
#[Controller]
final class Settings extends BaseController
{
	private AnilistModel $anilistModel;
	private SettingsModel $settingsModel;

	/**
	 * Settings constructor.
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->anilistModel = $container->get('anilist-model');
		$this->settingsModel = $container->get('settings-model');

		// This is a rare controller where every route is private
		$this->checkAuth();
	}

	/**
	 * Show the user settings, if logged in
	 */
	#[Route('settings', '/settings')]
	public function index(): void
	{
		$auth = $this->container->get('auth');
		$form = $this->settingsModel->getSettingsForm();

		$hasRequiredAnilistConfig = $this->config->has(['anilist', 'client_secret']) &&
			$this->config->has(['anilist', 'client_id']) &&
			$this->config->has(['anilist', 'username']);

		$this->outputHTML('settings/settings', [
			'anilistModel' => $this->anilistModel,
			'auth' => $auth,
			'form' => $form,
			'hasRequiredAnilistConfig' => $hasRequiredAnilistConfig,
			'config' => $this->config,
			'title' => $this->config->get('whose_list') . "'s Settings",
		]);
	}

	/**
	 * Attempt to save the user's settings
	 *
	 * @throws RouteNotFound
	 */
	#[Route('settings-post', '/settings/update', Route::POST)]
	public function update(): void
	{
		$post = (array) $this->request->getParsedBody();
		unset($post['settings-tabs']);

		$saved = $this->settingsModel->saveSettingsFile($post);

		$saved
			? $this->setFlashMessage('Saved config settings.', 'success')
			: $this->setFlashMessage('Failed to save config file.', 'error');

		$this->redirect($this->url->generate('settings'), 303);
	}

	/**
	 * Redirect to Anilist to start Oauth flow
	 */
	#[Route('anilist-redirect', '/anilist-redirect')]
	public function anilistRedirect(): void
	{
		$query = http_build_query([
			'client_id' => $this->config->get(['anilist', 'client_id']),
			'redirect_uri' => $this->urlGenerator->url('/anilist-oauth'),
			'response_type' => 'code',
		]);

		$redirectUrl = "https://anilist.co/api/v2/oauth/authorize?{$query}";

		$this->redirect($redirectUrl, 303);
	}

	/**
	 * Oauth callback for Anilist API
	 */
	#[Route('anilist-callback', '/anilist-oauth')]
	public function anilistCallback(): void
	{
		$query = $this->request->getQueryParams();
		$authCode = $query['code'];
		$uri = $this->urlGenerator->url('/anilist-oauth');

		$authData = $this->anilistModel->authenticate($authCode, $uri);
		$settings = $this->settingsModel->getSettings();

		if (array_key_exists('error', $authData))
		{
			$this->errorPage(400, 'Error Linking Account', $authData['hint']);

			return;
		}

		// Update the override config file
		$anilistSettings = [
			'access_token' => $authData['access_token'],
			'access_token_expires' => (time() - 10) + $authData['expires_in'],
			'refresh_token' => $authData['refresh_token'],
		];

		$newSettings = $settings;
		$newSettings['anilist'] = array_merge($settings['anilist'], $anilistSettings);

		foreach ($newSettings['config'] as $key => $value)
		{
			$newSettings[$key] = $value;
		}

		unset($newSettings['config']);

		$saved = $this->settingsModel->saveSettingsFile($newSettings);

		$saved
			? $this->setFlashMessage('Linked Anilist Account', 'success')
			: $this->setFlashMessage('Error Linking Anilist Account', 'error');

		$this->redirect($this->url->generate('settings'), 303);
	}
}
