<article class="media" id="a-<?= $item['hummingbird_id'] ?>">
	<?= $helper->picture("images/anime/{$item['hummingbird_id']}.webp") ?>
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
			<?php if ($item['episode_count'] > 1): ?>
			<div class="completion">Episodes: <?= $item['episode_count'] ?></div>
			<?php endif ?>
			<div class="media_type"><?= $item['show_type'] ?></div>
			<div class="age-rating"><?= $item['age_rating'] ?></div>
		</div>
	</div>
</article>