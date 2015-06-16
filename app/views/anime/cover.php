<main>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<a href="<?= $item['anime']['url'] ?>">
				<article class="media" id="a-<?= $item['anime']['id'] ?>">
					<img class="round_all" src="<?= $item['anime']['cover_image'] ?>" />
					<div class="round_all name">
						<?= $item['anime']['title'] ?>
						<?= ($item['anime']['alternate_title'] != "") ? "<br />({$item['anime']['alternate_title']})" : ""; ?>
					</div>
					<div class="media_metadata">
						<div class="round_top airing_status"><?= $item['anime']['status'] ?></div>
						<div class="user_rating"><?= (int)($item['rating']['value'] * 2) ?> / 10</div>
						<div class="round_bottom completion">Episodes: <?= $item['episodes_watched'] ?> / <?= $item['anime']['episode_count'] ?></div>
					</div>
					<div class="medium_metadata">
						<div class="round_top media_type"><?= $item['anime']['show_type'] ?></div>
						<div class="round_bottom age_rating"><?= $item['anime']['age_rating'] ?></div>
					</div>
				</article>
				</a>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
</main>
