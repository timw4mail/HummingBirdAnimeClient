<?php include 'header.php' ?>
<body>
	<h1>Tim's Anime List [<a href="//manga.timshomepage.net">Manga List</a>]</h1>
	<?php include 'anime_nav.php' ?>
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
							<div class="airing_status"><?= $item['anime']['status'] ?></div>
							<div class="user_rating"><?= (int)($item['rating']['value'] * 2) ?> / 10</div>
							<div class="completion">Episodes: <?= $item['episodes_watched'] ?> / <?= $item['anime']['episode_count'] ?></div>
						</div>
						<div class="medium_metadata">
							<div class="media_type"><?= $item['anime']['show_type'] ?></div>
							<div class="age_rating"><?= $item['anime']['age_rating'] ?></div>
						</div>
					</article>
					<?php endforeach ?>
				</section>
			</section>
		<?php endforeach ?>
	</main>
</body>
</html>