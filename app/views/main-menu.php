<?php declare(strict_types=1);

namespace Aviat\AnimeClient;

$whose = $config->get('whose_list') . "'s ";
$lastSegment = $urlGenerator->lastSegment();
$extraSegment = $lastSegment === 'list' ? '/list' : '';

?>
<h1 class="flex flex-align-end flex-wrap">
	<span class="flex-no-wrap grow-1">
		<?php if(strpos($route_path, 'collection') === FALSE): ?>
			<?= $whose . ucfirst($url_type) . ' List' ?>
			<?php if($config->get("show_{$url_type}_collection")): ?>
				[<?= $helper->a(
					$url->generate('collection.view'),
					ucfirst($url_type) . ' Collection'
				) ?>]
			<?php endif ?>
			[<?= $helper->a(
				$urlGenerator->defaultUrl($other_type) . $extraSegment,
				ucfirst($other_type) . ' List'
			) ?>]
		<?php else: ?>
			<?= $whose . ucfirst($url_type) . ' Collection' ?>
			[<?= $helper->a($urlGenerator->defaultUrl('anime'), 'Anime List') ?>]
			[<?= $helper->a($urlGenerator->defaultUrl('manga'), 'Manga List') ?>]
		<?php endif ?>
	</span>

	<span class="flex-no-wrap small-font">[<?= $helper->a(
		$url->generate('user_info'),
		'About '. $config->get('whose_list')
	) ?>]</span>

	<?php if ($auth->isAuthenticated()): ?>
		<span class="flex-no-wrap">&nbsp;</span>
		<span class="flex-no-wrap small-font">
			<button type="button" class="js-clear-cache user-btn">Clear API Cache</button>
		</span>
		<span class="flex-no-wrap">&nbsp;</span>
	<?php endif ?>

	<span class="flex-no-wrap small-font">
		<?php if ($auth->isAuthenticated()): ?>
			<?= $helper->a(
				$url->generate('logout'),
				'Logout',
				['class' => 'bracketed']
			) ?>
		<?php else: ?>
			[<?= $helper->a($url->generate('login'), "{$whose} Login") ?>]
		<?php endif ?>
	</span>
</h1>
<nav>
	<?php if ($container->get('util')->isViewPage()): ?>
		<?= $helper->menu($menu_name) ?>
		<br />
		<ul>
			<li class="<?= Util::isNotSelected('list', $lastSegment) ?>"><a href="<?= $urlGenerator->url($route_path) ?>">Cover View</a></li>
			<li class="<?= Util::isSelected('list', $lastSegment) ?>"><a href="<?= $urlGenerator->url("{$route_path}/list") ?>">List View</a></li>
		</ul>
	<?php endif ?>
</nav>
