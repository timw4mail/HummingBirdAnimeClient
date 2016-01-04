<main>
<?php if ($auth->is_authenticated()): ?>
<a class="bracketed" href="<?= $urlGenerator->url('collection/add', 'anime') ?>">Add Item</a>
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
						<?php if ($auth->is_authenticated()): ?>
							<div class="row">
								<span class="edit"><a class="bracketed" href="<?= $urlGenerator->url("collection/edit/{$item['hummingbird_id']}") ?>">Edit</a></span>
								<?php /*<span class="delete"><a class="bracketed" href="<?= $urlGenerator->url("collection/delete/{$item['hummingbird_id']}") ?>">Delete</a></span> */ ?>
							</div>
						<?php endif ?>
						<div class="row">
							<div class="completion">Episodes: <?= $item['episode_count'] ?></div>
							<div class="media_type"><?= $item['show_type'] ?></div>
							<div class="age_rating"><?= $item['age_rating'] ?></div>
						</div>
					</div>
				</article>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
<?php endif ?>
</main>
