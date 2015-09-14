<main>
<?php if (is_logged_in()): ?>
[<a href="<?= $config->full_url('collection/add', 'anime') ?>">Add Item</a>]
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<article class="media" id="a-<?= $item['hummingbird_id'] ?>">
					<img src="<?= $urlGenerator->asset_url('images', 'anime', basename($item['cover_image'])) ?>" />
					<div class="name">
						<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
						<?= $item['title'] ?>
						<?= ($item['alternate_title'] != "") ? "<br />({$item['alternate_title']})" : ""; ?>
						</a>
					</div>
					<div class="table">
						<div class="row">
							<div class="completion">Episodes: <?= $item['episode_count'] ?></div>
							<div class="media_type"><?= $item['show_type'] ?></div>
							<div class="age_rating"><?= $item['age_rating'] ?></div>
						</div>
					</div>
				</article>

				<?php if (is_logged_in()): ?>
					<span>[<a href="<?= $urlGenerator->full_url("collection/edit/{$item['hummingbird_id']}", "anime") ?>">Edit</a>]</span>
					<?php endif ?>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
<?php endif ?>
</main>