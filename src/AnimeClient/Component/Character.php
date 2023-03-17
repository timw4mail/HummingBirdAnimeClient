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

namespace Aviat\AnimeClient\Component;

final class Character
{
	use ComponentTrait;

	public function __invoke(string $name, string $link, string $picture, string $className = 'character'): string
	{
		return $this->render('character.php', [
			'name' => $name,
			'link' => $link,
			'picture' => $picture,
			'className' => $className,
		]);
	}
}
