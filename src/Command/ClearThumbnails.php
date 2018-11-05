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

namespace Aviat\AnimeClient\Command;

/**
 * Clears out image cache directories
 */
class ClearThumbnails extends BaseCommand {

	public function execute(array $args, array $options = []): void
	{
		$this->clearThumbs();
		$this->echoBox('All cached images have been removed');
	}

	public function clearThumbs()
	{
		$imgDir = realpath(__DIR__ . '/../../public/images');

		$paths = [
			'avatars/*.gif',
			'avatars/*.jpg',
			'avatars/*.png',
			'avatars/*.webp',
			'anime/*.jpg',
			'anime/*.png',
			'anime/*.webp',
			'manga/*.jpg',
			'manga/*.png',
			'manga/*.webp',
			'characters/*.jpg',
			'characters/*.png',
			'characters/*.webp',
			'people/*.jpg',
			'people/*.png',
			'people/*.webp',
		];

		foreach($paths as $path)
		{
			$cmd = "rm -rf {$imgDir}/{$path}";
			exec($cmd);
		}
	}
}