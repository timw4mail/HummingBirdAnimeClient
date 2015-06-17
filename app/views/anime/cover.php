<main>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<a href="<?= $item['anime']['url'] ?>">
				<article class="media" id="a-<?= $item['anime']['id'] ?>">
					<img src="<?= $item['anime']['cover_image'] ?>" />
					<div class="name">
						<?= $item['anime']['title'] ?>
						<?= ($item['anime']['alternate_title'] != "") ? "<br />({$item['anime']['alternate_title']})" : ""; ?>
					</div>
					<div class="table">
						<div class="row">
							<div class="user_rating">Rating: <?= (int)($item['rating']['value'] * 2) ?> / 10</div>
							<div class="completion">Episodes: <?= $item['episodes_watched'] ?>&nbsp;/&nbsp;<?= ($item['anime']['episode_count'] != 0) ? $item['anime']['episode_count'] : "-" ?></div>
						</div>
						<div class="row">
							<div class="media_type"><?= $item['anime']['show_type'] ?></div>
							<div class="airing_status"><?= $item['anime']['status'] ?></div>
							<div class="age_rating"><?= $item['anime']['age_rating'] ?></div>
						</div>
					</div>
				</article>
				</a>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
</main>
