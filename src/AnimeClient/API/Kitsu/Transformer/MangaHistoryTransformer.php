<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;

class MangaHistoryTransformer extends HistoryTransformer
{
	protected string $type = 'manga';
	protected string $progressAction = 'Read chapter';
	protected string $reconsumeAction = 'Reread chapter';
	protected string $largeAggregateAction = 'Blew through chapters';
	protected string $reconsumingStatus = 'Rereading';
	protected array $statusMap = MangaReadingStatus::KITSU_TO_TITLE;
}
