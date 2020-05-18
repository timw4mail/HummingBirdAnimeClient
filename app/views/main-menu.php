<?php declare(strict_types=1);

namespace Aviat\AnimeClient;

$whose = $config->get('whose_list') . "'s ";
$lastSegment = $urlGenerator->lastSegment();
$extraSegment = $lastSegment === 'list' ? '/list' : '';
$hasAnime = stripos($_SERVER['REQUEST_URI'], 'anime') !== FALSE;
$hasManga = stripos($_SERVER['REQUEST_URI'], 'manga') !== FALSE;

?>
<div id="main-nav" class="flex flex-align-end flex-wrap">
	<span class="flex-no-wrap grow-1">
		<?php if(strpos($route_path, 'collection') === FALSE): ?>
			<?= $helper->a(
				$urlGenerator->defaultUrl($url_type),
				$whose . ucfirst($url_type) . ' List',
					['aria-current'=> 'page']
			) ?>
			<?php if($config->get("show_{$url_type}_collection")): ?>
				[<?= $helper->a(
					$url->generate("{$url_type}.collection.view") . $extraSegment,
					ucfirst($url_type) . ' Collection'
				) ?>]
			<?php endif ?>
			<?php if($config->get("show_{$other_type}_collection")): ?>
				[<?= $helper->a(
					$url->generate("{$other_type}.collection.view") . $extraSegment,
					ucfirst($other_type) . ' Collection'
				) ?>]
			<?php endif ?>
			[<?= $helper->a(
				$urlGenerator->defaultUrl($other_type) . $extraSegment,
				ucfirst($other_type) . ' List'
			) ?>]
		<?php else: ?>
			<?= $helper->a(
					$url->generate("{$url_type}.collection.view") . $extraSegment,
					$whose . ucfirst($url_type) . ' Collection',
					['aria-current'=> 'page']
			) ?>
			<?php if($config->get("show_{$other_type}_collection")): ?>
				[<?= $helper->a(
					$url->generate("{$other_type}.collection.view") . $extraSegment,
					ucfirst($other_type) . ' Collection'
				) ?>]
			<?php endif ?>
			[<?= $helper->a($urlGenerator->defaultUrl('anime') . $extraSegment, 'Anime List') ?>]
			[<?= $helper->a($urlGenerator->defaultUrl('manga') . $extraSegment, 'Manga List') ?>]
		<?php endif ?>
		<?php if ($auth->isAuthenticated() && $config->get(['cache', 'driver']) !== 'null'): ?>
			<span class="flex-no-wrap small-font">
			<button type="button" class="js-clear-cache user-btn">Clear API Cache</button>
		</span>
		<?php endif ?>
	</span>

	<span class="flex-no-wrap small-font">[<?= $helper->a(
		$url->generate('default_user_info'),
		'About '. $config->get('whose_list')
	) ?>]</span>

	<?php if ($auth->isAuthenticated()): ?>
		<span class="flex-no-wrap small-font">
		<?= $helper->a(
			$url->generate('settings'),
			'Settings',
			['class' => 'bracketed']
		) ?>
		</span>
		<span class="flex-no-wrap small-font">
		<?= $helper->a(
			$url->generate('logout'),
			'Logout',
			['class' => 'bracketed']
		) ?>
		</span>
	<?php else: ?>
		<span class="flex-no-wrap small-font">
		[<?= $helper->a($url->generate('login'), "{$whose} Login") ?>]
		</span>
	<?php endif ?>
</div>
<?php if ($container->get('util')->isViewPage() && ($hasAnime || $hasManga)): ?>
<nav>
		<?= $helper->menu($menu_name) ?>
		<?php if (stripos($_SERVER['REQUEST_URI'], 'history') === FALSE): ?>
		<br />
		<ul>
			<?php $currentView = Util::eq('list', $lastSegment) ? 'list' : 'cover' ?>
			<li class="<?= Util::isNotSelected('list', $lastSegment) ?>">
				<a aria-current="<?= Util::ariaCurrent($currentView === 'cover') ?>"
						href="<?= $urlGenerator->url($route_path) ?>">Cover View</a>
			</li>
			<li class="<?= Util::isSelected('list', $lastSegment) ?>">
				<a aria-current="<?= Util::ariaCurrent($currentView === 'list') ?>"
						href="<?= $urlGenerator->url("{$route_path}/list") ?>">List View</a>
			</li>
		</ul>
		<?php endif ?>
</nav>
<?php endif ?>
