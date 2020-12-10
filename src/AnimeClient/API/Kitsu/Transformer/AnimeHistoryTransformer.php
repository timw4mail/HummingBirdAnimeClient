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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;

class AnimeHistoryTransformer extends HistoryTransformer {
	protected string $type = 'anime';

	protected string $progressAction = 'Watched episode';

	protected string $reconsumeAction = 'Rewatched episode';

	protected string $largeAggregateAction = 'Marathoned episodes';

	protected string $reconsumingStatus = 'Rewatching';

	protected array $statusMap = AnimeWatchingStatus::KITSU_TO_TITLE;
}