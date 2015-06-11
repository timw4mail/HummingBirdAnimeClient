<body class="anime collection">
	<h1><?= WHOSE ?> Anime Collection [<a href="<?= full_url('', 'manga') ?>">Manga List</a>]</h1>
	<?php include 'nav.php' ?>
	<main>
		<?php foreach ($sections as $name => $items): ?>
			<section class="status">
				<h2><?= $name ?></h2>
				<section class="media-wrap">
					<?php foreach($items as $item): ?>
					<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
					<article class="media" id="a-<?= $item['hummingbird_id'] ?>">
						<img src="<?= $item['cover_image'] ?>" />
						<div class="name">
							<?= $item['title'] ?>
							<?= ($item['alternate_title'] != "") ? "<br />({$item['alternate_title']})" : ""; ?>
						</div>
						<div class="media_metadata">
							<div class="completion">Episodes: <?= $item['episode_count'] ?></div>
						</div>
						<div class="medium_metadata">
							<div class="media_type"><?= $item['show_type'] ?></div>
							<div class="age_rating"><?= $item['age_rating'] ?></div>
						</div>
					</article>
					</a>
					<?php endforeach ?>
				</section>
			</section>
		<?php endforeach ?>
	</main>
</body>
</html>