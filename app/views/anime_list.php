<?php include 'header.php' ?>
<body>
	<h1>Tim's Anime List</h1>
	<nav>
		<ul>
			<li class="<?= is_selected('/all', $route_path) ?>"><a href="/all">All</a></li>
			<li class="<?= is_selected('/', $route_path) ?>"><a href="/">Watching</a></li>
			<li class="<?= is_selected('/plan_to_watch', $route_path) ?>"><a href="/plan_to_watch">Plan to Watch</a></li>
			<li class="<?= is_selected('/on_hold', $route_path) ?>"><a href="/on_hold">On Hold</a></li>
			<li class="<?= is_selected('/dropped', $route_path) ?>"><a href="/dropped">Dropped</a></li>
			<li class="<?= is_selected('/completed', $route_path) ?>"><a href="/completed">Completed</a></li>
		</ul>
	</nav>
	<main>
		<?php foreach ($sections as $name => $items): ?>
			<section class="status">
				<h2><?= $name ?></h2>
				<section class="media-wrap">
					<?php foreach($items as $item): ?>
					<article class="media" id="a-<?= $item['anime']['id'] ?>">
						<img src="<?= $item['anime']['cover_image'] ?>" />
						<div class="name"><a href="<?= $item['anime']['url'] ?>">
							<?= $item['anime']['title'] ?>
							<?= ($item['anime']['alternate_title'] != "") ? "<br />({$item['anime']['alternate_title']})" : ""; ?>
						</a></div>
						<div class="media_metadata">
							<div class="media_type"><?= $item['anime']['show_type'] ?></div>
							<div class="airing_status"><?= $item['anime']['status'] ?></div>
							<div class="user_rating"><?= (int)($item['rating']['value'] * 2) ?> / 10</div>
							<div class="completion"><?= $item['episodes_watched'] ?> / <?= $item['anime']['episode_count'] ?></div>
						</div>
					</article>
					<?php endforeach ?>
				</section>
			</section>
		<?php endforeach ?>
	</main>
</body>
</html>