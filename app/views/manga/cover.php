<main>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<a href="https://hummingbird.me/manga/<?= $item['manga']['id'] ?>">
				<article class="media" id="manga-<?= $item['manga']['id'] ?>">
					<img src="<?= $item['manga']['poster_image'] ?>" />
					<div class="name">
						<?= $item['manga']['romaji_title'] ?>
						<?= (isset($item['manga']['english_title'])) ? "<br />({$item['manga']['english_title']})" : ""; ?>
					</div>
					<div class="media_metadata">
						<div class="user_rating"><?= ($item['rating'] > 0) ? (int)($item['rating'] * 2) : '-' ?> / 10</div>
						<div class="completion">
							Chapters: <?= $item['chapters_read'] ?> / <?= ($item['manga']['chapter_count'] > 0) ? $item['manga']['chapter_count'] : "-" ?><?php /*<br />
							Volumes: <?= $item['volumes_read'] ?> / <?= ($item['manga']['volume_count'] > 0) ? $item['manga']['volume_count'] : "-" ?>*/ ?>
						</div>
					</div>
					<?php /*<div class="medium_metadata">
						<div class="media_type"><?= $item['manga']['manga_type'] ?></div>
					</div> */ ?>
				</article>
				</a>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
</main>