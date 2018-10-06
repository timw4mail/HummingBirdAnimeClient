<article class="media" id="a-<?= $item['hummingbird_id'] ?>">
	<picture>
		<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$item['hummingbird_id']}.webp") ?>" type="image/webp">
		<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$item['hummingbird_id']}.jpg") ?>" type="image/jpeg">
		<img src="<?= $urlGenerator->assetUrl("images/anime/{$item['hummingbird_id']}.jpg") ?>" alt="<?= $item['title'] ?> cover image" />
	</picture>

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