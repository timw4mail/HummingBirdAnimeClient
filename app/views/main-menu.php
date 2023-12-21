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

namespace Aviat\AnimeClient;

$whose = $_->config->get('whose_list') . "'s ";
$lastSegment = $_->lastSegment();
$extraSegment = $lastSegment === 'list' ? '/list' : '';
$hasAnime = str_contains($GLOBALS['_SERVER']['REQUEST_URI'], 'anime');
$hasManga = str_contains($GLOBALS['_SERVER']['REQUEST_URI'], 'manga');

?>
<div id="main-nav" class="flex flex-align-end flex-wrap">
	<span class="flex-no-wrap grow-1">
		<?php if( ! str_contains($route_path, 'collection')): ?>
			<?= $_->h->a(
				$_->defaultUrl($url_type),
				$whose . ucfirst($url_type) . ' List',
					['aria-current'=> 'page']
			) ?>
			<?php if($_->config->get("show_{$url_type}_collection")): ?>
				[<?= $_->h->a(
					$_->urlFromRoute("{$url_type}.collection.view") . $extraSegment,
					ucfirst($url_type) . ' Collection'
				) ?>]
			<?php endif ?>
			<?php if($_->config->get("show_{$other_type}_collection")): ?>
				[<?= $_->h->a(
					$_->urlFromRoute("{$other_type}.collection.view") . $extraSegment,
					ucfirst($other_type) . ' Collection'
				) ?>]
			<?php endif ?>
			[<?= $_->h->a(
				$_->defaultUrl($other_type) . $extraSegment,
				ucfirst($other_type) . ' List'
			) ?>]
		<?php else: ?>
			<?= $_->h->a(
					$_->urlFromRoute("{$url_type}.collection.view") . $extraSegment,
					$whose . ucfirst($url_type) . ' Collection',
					['aria-current'=> 'page']
			) ?>
			<?php if($_->config->get("show_{$other_type}_collection")): ?>
				[<?= $_->h->a(
					$_->urlFromRoute("{$other_type}.collection.view") . $extraSegment,
					ucfirst($other_type) . ' Collection'
				) ?>]
			<?php endif ?>
			[<?= $_->h->a($_->defaultUrl('anime') . $extraSegment, 'Anime List') ?>]
			[<?= $_->h->a($_->defaultUrl('manga') . $extraSegment, 'Manga List') ?>]
		<?php endif ?>
		<?php if ($_->isAuthenticated() && $_->config->get(['cache', 'driver']) !== 'null'): ?>
			<span class="flex-no-wrap small-font">
			<button type="button" class="js-clear-cache user-btn">Clear API Cache</button>
		</span>
		<?php endif ?>
	</span>

	<span class="flex-no-wrap small-font">[<?= $_->h->a(
		$_->urlFromRoute('default_user_info'),
		'About '. $_->config->get('whose_list')
	) ?>]</span>

	<?php if ($_->isAuthenticated()): ?>
		<span class="flex-no-wrap small-font">
		<?= $_->h->a(
			$_->urlFromRoute('settings'),
			'Settings',
			['class' => 'bracketed']
		) ?>
		</span>
		<span class="flex-no-wrap small-font">
		<?= $_->h->a(
			$_->urlFromRoute('logout'),
			'Logout',
			['class' => 'bracketed']
		) ?>
		</span>
	<?php else: ?>
		<span class="flex-no-wrap small-font">
		[<?= $_->h->a($_->urlFromRoute('login'), "{$whose} Login") ?>]
		</span>
	<?php endif ?>
</div>
<?php if ($_->isViewPage() && ($hasAnime || $hasManga)): ?>
<nav>
		<?= $_->h->menu($menu_name) ?>
		<?php if (stripos($GLOBALS['_SERVER']['REQUEST_URI'], 'history') === FALSE): ?>
		<br />
		<ul>
			<?php $currentView = Util::eq('list', $lastSegment) ? 'list' : 'cover' ?>
			<li class="<?= Util::isNotSelected('list', $lastSegment) ?>">
				<a aria-current="<?= Util::ariaCurrent($currentView === 'cover') ?>"
						href="<?= $_->urlFromPath($route_path) ?>">Cover View</a>
			</li>
			<li class="<?= Util::isSelected('list', $lastSegment) ?>">
				<a aria-current="<?= Util::ariaCurrent($currentView === 'list') ?>"
						href="<?= $_->urlFromPath("{$route_path}/list") ?>">List View</a>
			</li>
		</ul>
		<?php endif ?>
</nav>
<?php endif ?>
