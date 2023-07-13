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

namespace Aviat\AnimeClient\Command;

/**
 * Clears out image cache directories
 */
class ClearThumbnails extends BaseCommand
{
	public function execute(array $args, array $options = []): void
	{
		$this->clearThumbs();
		$this->echoBox('All cached images have been removed');
	}

	private function clearThumbs(): void
	{
		$imgDir = dirname(__DIR__, 3) . '/public/images';

		$paths = [
			'anime/*.jpg',
			'anime/*.png',
			'anime/*.webp',
			'avatars/*.gif',
			'avatars/*.jpg',
			'avatars/*.png',
			'avatars/*.webp',
			'characters/*.jpg',
			'characters/*.png',
			'characters/*.webp',
			'manga/*.jpg',
			'manga/*.png',
			'manga/*.webp',
			'people/*.jpg',
			'people/*.png',
			'people/*.webp',
		];

		foreach ($paths as $path)
		{
			$cmd = "find {$imgDir} -path \"*/{$path}\" | xargs rm -f";
			exec($cmd);
		}
	}
}
