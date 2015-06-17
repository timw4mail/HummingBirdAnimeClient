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
					<div class="table">
						<div class="row">
							<div class="completion">Episodes: <?= $item['episode_count'] ?></div>
							<div class="media_type"><?= $item['show_type'] ?></div>
							<div class="age_rating"><?= $item['age_rating'] ?></div>
						</div>
					</div>
					
				</article>
				</a>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
</main>