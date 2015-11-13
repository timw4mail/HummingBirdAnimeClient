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
					<?php if ($auth->is_authenticated()): ?>
					<div class="edit_buttons" hidden>
						<button class="plus_one_chapter">+1 Chapter</button>
						<button class="plus_one_volume">+1 Volume</button>
					</div>
					<?php endif ?>
					<img src="<?= $escape->attr($item['manga']['image']) ?>" />
					<div class="name">
						<a href="<?= $item['manga']['url'] ?>">
						<?= $escape->html($item['manga']['title']) ?>
						<?= (isset($item['manga']['alternate_title'])) ? "<br />({$item['manga']['alternate_title']})" : ""; ?>
						</a>
					</div>
					<div class="table">
						<div class="row">
							<div class="user_rating">Rating: <?= $item['user_rating'] ?> / 10</div>
						</div>
						<div class="row">
							<div class="chapter_completion">
								Chapters: <span class="chapters_read"><?= $item['chapters']['read'] ?></span> /
									<span class="chapter_count"><?= $item['chapters']['total'] ?></span>
							</div>
						</div>
						<div class="row">
							<div class="volume_completion">
								Volumes: <span class="volumes_read"><?= $item['volumes']['read'] ?></span> /
									<span class="volume_count"><?= $item['volumes']['total'] ?></span>
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
<?php if ($auth->is_authenticated()): ?>
<script src="<?= $urlGenerator->asset_url('js.php?g=edit') ?>"></script>
<?php endif ?>
