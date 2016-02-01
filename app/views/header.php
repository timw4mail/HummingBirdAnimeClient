<?php namespace Aviat\AnimeClient ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $title ?></title>
	<meta charset="utf-8" />
	<meta http-equiv="cache-control" content="no-store" />
	<meta http-equiv="Content-Security-Policy" content="script-src 'self'" />
	<link rel="stylesheet" href="<?= $urlGenerator->asset_url('css.php?g=base') ?>" />
	<script src="<?= $urlGenerator->asset_url('js.php?g=base') ?>"></script>
</head>
<body class="<?= $escape->attr($url_type) ?> list">
	<header>
		<h1 class="flex flex-align-end flex-wrap">
			<span class="flex-no-wrap grow-1">
				<?php if(strpos($route_path, 'collection') === FALSE): ?>
					<a href="<?= $escape->attr($urlGenerator->default_url($url_type)) ?>">
						<?= $config->get('whose_list') ?>'s <?= ucfirst($url_type) ?> List
					</a>
					<?php if($config->get("show_{$url_type}_collection")): ?>
						[<a href="<?= $urlGenerator->url('collection/view') ?>"><?= ucfirst($url_type) ?> Collection</a>]
					<?php endif ?>
					[<a href="<?= $urlGenerator->default_url($other_type) ?>"><?= ucfirst($other_type) ?> List</a>]
				<?php else: ?>
					<a href="<?= $urlGenerator->url('collection/view') ?>">
						<?= $config->get('whose_list') ?>'s <?= ucfirst($url_type) ?> Collection
					</a>
					[<a href="<?= $urlGenerator->default_url('anime') ?>">Anime List</a>]
					[<a href="<?= $urlGenerator->default_url('manga') ?>">Manga List</a>]
				<?php endif ?>
			</span>
			<span class="flex-no-wrap small-font">
				<?php if ($auth->is_authenticated()): ?>
				<a class="bracketed" href="<?= $urlGenerator->url("/{$url_type}/logout", $url_type) ?>">Logout</a>
				<?php else: ?>
				[<a href="<?= $urlGenerator->url("/{$url_type}/login", $url_type) ?>"><?= $config->get('whose_list') ?>'s Login</a>]
				<?php endif ?>
			</span>
		</h1>
		<nav>
			<?php if ($container->get('anime-client')->is_view_page()): ?>
			<?= $helper->menu($menu_name) ?>
			<br />
			<ul>
				<li class="<?= AnimeClient::is_not_selected('list', $urlGenerator->last_segment()) ?>"><a href="<?= $urlGenerator->url($route_path) ?>">Cover View</a></li>
				<li class="<?= AnimeClient::is_selected('list', $urlGenerator->last_segment()) ?>"><a href="<?= $urlGenerator->url("{$route_path}/list") ?>">List View</a></li>
			</ul>
			<?php endif ?>
		</nav>
		<?php if(isset($message) && is_array($message)):
	 		extract($message);
	 		include 'message.php';
	 	endif ?>
	</header>