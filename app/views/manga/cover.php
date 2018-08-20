<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate('manga.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<?php if (empty($items)): ?>
		<section class="status">
			<h2><?= $escape->html($name) ?></h2>
			<h3>There's nothing here!</h3>
		</section>
	<?php else: ?>
		<section class="status">
			<h2><?= $escape->html($name) ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<article class="media" data-kitsu-id="<?= $item['id'] ?>" data-mal-id="<?= $item['mal_id'] ?>">
					<?php if ($auth->isAuthenticated()): ?>
					<div class="edit_buttons" hidden>
						<button class="plus_one_chapter">+1 Chapter</button>
						<?php /* <button class="plus_one_volume">+1 Volume</button> */ ?>
					</div>
					<?php endif ?>
					<img src="<?= $urlGenerator->assetUrl('images/manga', "{$item['manga']['id']}.jpg") ?>" />
					<div class="name">
						<a href="<?= $url->generate('manga.details', ['id' => $item['manga']['slug']]) ?>">
						<?= $escape->html($item['manga']['title']) ?>
                        <?php foreach($item['manga']['titles'] as $title): ?>
                            <br /><small><?= $title ?></small>
                        <?php endforeach ?>
						</a>
					</div>
					<div class="table">
						<?php if ($auth->isAuthenticated()): ?>
						<div class="row">
							<span class="edit">
								<a class="bracketed"
									title="Edit information about this manga"
									href="<?= $url->generate('edit', [
										'controller' => 'manga',
										'id' => $item['id'],
										'status' => $name
									]) ?>">
									Edit
								</a>
							</span>
						</div>
						<?php endif ?>
						<div class="row">
							<div class="user_rating">Rating: <?= $item['user_rating'] ?> / 10</div>
						</div>

						<?php if ($item['rereading']): ?>
						<div class="row">
							<?php foreach(['rereading'] as $attr): ?>
							<?php if($item[$attr]): ?>
								<span class="item-<?= $attr ?>"><?= ucfirst($attr) ?></span>
							<?php endif ?>
							<?php endforeach ?>
						</div>
						<?php endif ?>

						<?php if ($item['reread'] > 0): ?>
						<div class="row">
							<div>Reread <?= $item['reread'] ?> time(s)</div>
						</div>
						<?php endif ?>

						<div class="row">
							<div class="chapter_completion">
								Chapters: <span class="chapters_read"><?= $item['chapters']['read'] ?></span> /
									<span class="chapter_count"><?= $item['chapters']['total'] ?></span>
							</div>
						<?php /* </div>
						<div class="row"> */ ?>
							<div class="volume_completion">
								Volumes: <span class="volume_count"><?= $item['volumes']['total'] ?></span>
							</div>
						</div>
					</div>
				</article>
				<?php endforeach ?>
			</section>
		</section>
	<?php endif ?>
	<?php endforeach ?>
<?php endif ?>
</main>
