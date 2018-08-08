<main>
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<div class="tabs">
	<?php $i = 0; ?>
	<?php foreach ($sections as $name => $items): ?>
		<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="collection-tab-<?= $i ?>" name="collection-tabs" />
		<label for="collection-tab-<?= $i ?>"><?= $name ?></label>
		<div class="content">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach ($items as $item): ?>
					<article class="media" id="a-<?= $item['hummingbird_id'] ?>">
						<img src="<?= $urlGenerator->assetUrl("images/anime/{$item['hummingbird_id']}.jpg") ?>"
								alt="<?= $item['title'] ?> cover image"/>
						<div class="name">
							<a href="<?= $url->generate('anime.details', ['id' => $item['slug']]) ?>">
								<?= $item['title'] ?>
								<?= ($item['alternate_title'] != "") ? "<small><br />{$item['alternate_title']}</small>" : ""; ?>
							</a>
						</div>
						<div class="table">
							<?php if ($auth->isAuthenticated()): ?>
								<div class="row">
							<span class="edit">
								<a class="bracketed"
									href="<?= $url->generate($collection_type . '.collection.edit.get', [
										'id' => $item['hummingbird_id']
									]) ?>">Edit</a>
							</span>
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
		</div>
		<?php $i++; ?>
	<?php endforeach ?>
	</div>
<?php endif ?>
</main>
