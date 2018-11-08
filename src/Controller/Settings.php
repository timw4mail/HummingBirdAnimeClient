<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for user settings
 */
final class Settings extends BaseController {
	/**
	 * @var \Aviat\AnimeClient\API\Anilist\Model
	 */
	private $anilistModel;

	/**
	 * @var \Aviat\AnimeClient\Model\Settings
	 */
	private $settingsModel;

	/**
	 * Settings constructor.
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);

		$this->anilistModel = $container->get('anilist-model');
		$this->settingsModel = $container->get('settings-model');
	}

	/**
	 * Show the user settings, if logged in
	 */
	public function index(): void
	{
		$auth = $this->container->get('auth');
		$form = $this->settingsModel->getSettingsForm();

		$hasAnilistLogin = $this->config->has(['anilist', 'access_token']);

		$this->outputHTML('settings/settings', [
			'anilistModel' => $this->anilistModel,
			'auth' => $auth,
			'form' => $form,
			'hasAnilistLogin' => $hasAnilistLogin,
			'config' => $this->config,
			'title' => $this->config->get('whose_list') . "'s Settings",
		]);
	}

	/**
	 * Attempt to save the user's settings
	 *
	 * @throws \Aura\Router\Exception\RouteNotFound
	 */
	public function update(): void
	{
		$post = $this->request->getParsedBody();
		unset($post['settings-tabs']);

		// dump($post);
		$saved = $this->settingsModel->saveSettingsFile($post);

		if ($saved)
		{
			$this->setFlashMessage('Saved config settings.', 'success');
		} else
		{
			$this->setFlashMessage('Failed to save config file.', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
	}

	/**
	 * Redirect to Anilist to start Oauth flow
	 */
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

		if ($saved)
		{
			$this->setFlashMessage('Linked Anilist Account', 'success');
		} else
		{
			$this->setFlashMessage('Error Linking Anilist Account', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
	}
}