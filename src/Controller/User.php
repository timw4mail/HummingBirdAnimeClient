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
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Controller for handling routes that don't fit elsewhere
 */
final class User extends BaseController {

	private $kitsuModel;

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

		$data = $this->kitsuModel->getUserData($username);
		$orgData = JsonAPI::organizeData($data)[0];
		$rels = $orgData['relationships'] ?? [];
		$favorites = array_key_exists('favorites', $rels) ? $rels['favorites'] : [];

		$stats = [];
		foreach ($rels['stats'] as $sid => &$item)
		{
			$key = $item['attributes']['kind'];
			$stats[$key] = $item['attributes']['statsData'];
			unset($item);
		}

		//dump($orgData);
		// dump($stats);

		// $timeOnAnime = $this->formatAnimeTime($orgData['attributes']['lifeSpentOnAnime']);
		$timeOnAnime = $this->formatAnimeTime($stats['anime-amount-consumed']['time']);


		$whom = $isMainUser
			? $this->config->get('whose_list')
			: $username;

		$this->outputHTML('user/details', [
			'title' => 'About ' . $whom,
			'data' => $orgData,
			'attributes' => $orgData['attributes'],
			'relationships' => $rels,
			'favorites' => $this->organizeFavorites($favorites),
			'stats' => $stats,
			'timeOnAnime' => $timeOnAnime,
		]);
	}
	
	
	/**
	 * Redirect to Anilist to start Oauth flow
	 */
	public function anilistRedirect()
	{
		$redirectUrl = 'https://anilist.co/api/v2/oauth/authorize?' .
			http_build_query([
				'client_id' => $this->config->get(['anilist', 'client_id']),
				'redirect_uri' => $this->urlGenerator->url('/anilist-oauth'),
				'response_type' => 'code',
			]);

		$this->redirect($redirectUrl, 303);
	}

	/**
	 * Oauth callback for Anilist API
	 */
	public function anilistCallback()
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

		foreach($newSettings['config'] as $key => $value)
		{
			$newSettings[$key] = $value;
		}
		unset($newSettings['config']);

		$saved = $this->settingsModel->saveSettingsFile($newSettings);

		if ($saved)
		{
			$this->setFlashMessage('Linked Anilist Account', 'success');
		}
		else
		{
			$this->setFlashMessage('Error Linking Anilist Account', 'error');
		}

		$this->redirect($this->url->generate('settings'), 303);
	}

	/**
	 * Reorganize favorites data to be more useful
	 *
	 * @param array $rawfavorites
	 * @return array
	 */
	private function organizeFavorites(array $rawfavorites): array
	{
		$output = [];

		unset($rawfavorites['data']);

		foreach ($rawfavorites as $item)
		{
			$rank = $item['attributes']['favRank'];
			foreach ($item['relationships']['item'] as $key => $fav)
			{
				$output[$key] = $output[$key] ?? [];
				foreach ($fav as $id => $data)
				{
					$output[$key][$rank] = array_merge(['id' => $id], $data['attributes']);
				}
			}

			ksort($output[$key]);
		}

		return $output;
	}

	/**
	 * Format the time spent on anime in a more readable format
	 *
	 * @param int $minutes
	 * @return string
	 */
	private function formatAnimeTime(int $minutes): string
	{
		$minutesPerDay = 1440;
		$minutesPerYear = $minutesPerDay * 365;

		// Minutes short of a year
		$years = (int)floor($minutes / $minutesPerYear);
		$minutes %= $minutesPerYear;

		// Minutes short of a day
		$extraMinutes = $minutes % $minutesPerDay;

		$days = ($minutes - $extraMinutes) / $minutesPerDay;

		// Minutes short of an hour
		$remMinutes = $extraMinutes % 60;

		$hours = ($extraMinutes - $remMinutes) / 60;

		$output = "{$days} days, {$hours} hours, and {$remMinutes} minutes.";

		if ($years > 0)
		{
			$output = "{$years} year(s),{$output}";
		}

		return $output;
	}
}