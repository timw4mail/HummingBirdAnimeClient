<main>
<?php if ($auth->is_authenticated()): ?>
<a class="bracketed" href="<?= $urlGenerator->url('manga/add') ?>">Add Item</a>
<?php endif ?>
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
					</div>
					<?php endif ?>
					<img src="<?= $escape->attr($item['manga']['image']) ?>" />
					<div class="name">
						<a href="<?= $url->generate('manga.details', ['id' => $item['manga']['slug']]) ?>">
						<?= $escape->html(array_shift($item['manga']['titles'])) ?>
                        <?php foreach($item['manga']['titles'] as $title): ?>
                            <br /><small><?= $title ?></small>
                        <?php endforeach ?>
						</a>
					</div>
					<div class="table">
						<?php if ($auth->is_authenticated()): ?>
						<div class="row">
							<span class="edit">
								<a class="bracketed" title="Edit information about this manga" href="<?= $urlGenerator->url("manga/edit/{$item['id']}/{$name}") ?>">Edit</a>
							</span>
						</div>
						<?php endif ?>
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
								Volumes: <span class="volume_count"><?= $item['volumes']['total'] ?></span>
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
<script src="<?= $urlGenerator->asset_url('js.php/g/edit') ?>"></script>
<?php endif ?>