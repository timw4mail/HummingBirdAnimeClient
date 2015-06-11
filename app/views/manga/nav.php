<nav>
	<ul>
		<?php foreach($nav_routes as $title => $path): ?>
		<li class="<?= is_selected($path, $route_path) ?>"><a href="<?= full_url($path, 'manga') ?>"><?= $title ?></a></li>
		<?php endforeach ?>
	</ul>
</nav>