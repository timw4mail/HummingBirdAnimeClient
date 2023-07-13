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

namespace Aviat\AnimeClient\Component;

final class Media
{
	use ComponentTrait;

	public function __invoke(array $titles, string $link, string $picture, string $className = 'media'): string
	{
		return $this->render('media.php', [
			'titles' => $titles,
			'link' => $link,
			'picture' => $picture,
			'className' => $className,
		]);
	}
}
