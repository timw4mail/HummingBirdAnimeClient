<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $title ?></title>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="<?= asset_url('css.php?g=base') ?>" />
	<script>
		var BASE_URL = "<?= base_url($url_type) ?>";
		var CONTROLLER = "<?= $url_type ?>";
	</script>
</head>
<body class="<?= $url_type ?> list">
	<h1 class="flex flex-align-end flex-wrap">
		<span class="flex-no-wrap grow-1"><?= WHOSE ?> <?= ucfirst($url_type) ?> <?= (strpos($route_path, 'collection') !== FALSE) ? 'Collection' : 'List' ?> [<a href="<?= full_url("", $other_type) ?>"><?= ucfirst($other_type) ?> List</a>]</span>
		<span class="flex-no-wrap small-font">
			<?php if (is_logged_in()): ?>
			[<a href="<?= full_url("/logout", $url_type) ?>">Logout</a>]
			<?php else: ?>
			[<a href="<?= full_url("/login", $url_type) ?>"><?= WHOSE ?> Login</a>]
			<?php endif ?>
		</span>
	</h1>
	<nav>
		<ul>
			<?php foreach($nav_routes as $title => $nav_path): ?>
			<li class="<?= is_selected($nav_path, $route_path) ?>"><a href="<?= full_url($nav_path, $url_type) ?>"><?= $title ?></a></li>
			<?php endforeach ?>
		</ul>
		<br />
		<ul>
			<li class="<?= is_not_selected('list', last_segment()) ?>"><a href="<?= full_url($route_path, $url_type) ?>">Cover View</a></li>
			<li class="<?= is_selected('list', last_segment()) ?>"><a href="<?= full_url("{$route_path}/list", $url_type) ?>">List View</a></li>
		</ul>
	</nav>
	<br />