<main>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $escape->html($name) ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<article class="media" id="manga-<?= $item['id'] ?>">
					<?php if (is_logged_in()): ?>
					<div class="edit_buttons" hidden>
						<button class="plus_one_chapter">+1 Chapter</button>
						<button class="plus_one_volume">+1 Volume</button>
					</div>
					<?php endif ?>
					<img src="<?= $escape->attr($item['manga']['poster_image']) ?>" />
					<div class="name">
						<a href="https://hummingbird.me/manga/<?= $item['manga']['id'] ?>">
						<?= $escape->html($item['manga']['romaji_title']) ?>
						<?= (isset($item['manga']['english_title'])) ? "<br />({$item['manga']['english_title']})" : ""; ?>
						</a>
					</div>
					<div class="table">
						<div class="row">
							<div class="user_rating">Rating: <?= ($item['rating'] > 0) ? (int)($item['rating'] * 2) : '-' ?> / 10</div>
						</div>
						<div class="row">
							<div class="chapter_completion">
								Chapters: <span class="chapters_read"><?= $item['chapters_read'] ?></span> /
									<span class="chapter_count"><?= ($item['manga']['chapter_count'] > 0) ? $item['manga']['chapter_count'] : "-" ?></span>
							</div>
						</div>
						<div class="row">
							<div class="volume_completion">
								Volumes: <span class="volumes_read"><?= $item['volumes_read'] ?></span> /
									<span class="volume_count"><?= ($item['manga']['volume_count'] > 0) ? $item['manga']['volume_count'] : "-" ?></span>
							</div>
						</div>
					</div>
				</article>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
<?php endif ?>
</main>
<?php if (is_logged_in()): ?>
<script src="<?= $urlGenerator->asset_url('js.php?g=edit') ?>"></script>
<?php endif ?>