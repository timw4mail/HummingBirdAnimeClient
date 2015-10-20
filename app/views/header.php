<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $title ?></title>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="<?= $urlGenerator->asset_url('css.php?g=base') ?>" />
	<script>
		var BASE_URL = "<?= $urlGenerator->base_url($url_type) ?>";
		var CONTROLLER = "<?= $url_type ?>";
	</script>
</head>
<body class="<?= $escape->attr($url_type) ?> list">
	<h1 class="flex flex-align-end flex-wrap">
		<span class="flex-no-wrap grow-1">
			<a href="<?= $escape->attr($urlGenerator->default_url($url_type)) ?>">
				<?= $config->get('whose_list') ?>'s <?= ucfirst($url_type) ?> <?= (strpos($route_path, 'collection') !== FALSE) ? 'Collection' : 'List' ?>
			</a> [<a href="<?= $urlGenerator->default_url($other_type) ?>"><?= ucfirst($other_type) ?> List</a>]
		</span>
		<span class="flex-no-wrap small-font">
			<?php /*if (is_logged_in()): ?>
			[<a href="<?= $urlGenerator->url("/{$url_type}/logout", $url_type) ?>">Logout</a>]
			<?php else: ?>
			[<a href="<?= $urlGenerator->url("/{$url_type}/login", $url_type) ?>"><?= $config->whose_list ?>'s Login</a>]
			<?php endif */ ?>
		</span>
	</h1>
	<nav>
		<?= $helper->menu($menu_name) ?>
		<?php if (is_view_page()): ?>
		<br />
		<ul>
			<li class="<?= is_not_selected('list', $urlGenerator->last_segment()) ?>"><a href="<?= $urlGenerator->url($route_path) ?>">Cover View</a></li>
			<li class="<?= is_selected('list', $urlGenerator->last_segment()) ?>"><a href="<?= $urlGenerator->url("{$route_path}/list") ?>">List View</a></li>
		</ul>
		<?php endif ?>
	</nav>
	<br />
