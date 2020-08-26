<article
	class="media"
	data-kitsu-id="<?= $item['id'] ?>"
	data-mal-id="<?= $item['mal_id'] ?>"
>
	<?php if ($auth->isAuthenticated()): ?>
		<button title="Increment episode count" class="plus-one" hidden>+1 Episode</button>
	<?php endif ?>
	<?= $helper->picture("images/anime/{$item['anime']['id']}.webp") ?>

	<div class="name">
		<a href="<?= $url->generate('anime.details', ['id' => $item['anime']['slug']]) ?>">
			<span class="canonical"><?= $item['anime']['title'] ?></span>
			<?php foreach ($item['anime']['titles'] as $title): ?>
				<br/>
				<small><?= $title ?></small>
			<?php endforeach ?>
		</a>
	</div>
	<div class="table">
		<?php if ($item['private'] || $item['rewatching']): ?>
			<div class="row">
				<?php foreach (['private', 'rewatching'] as $attr): ?>
					<?php if ($item[$attr]): ?>
						<span class="item-<?= $attr ?>"><?= ucfirst($attr) ?></span>
					<?php endif ?>
				<?php endforeach ?>
			</div>
		<?php endif ?>

		<?php if ($item['rewatched'] > 0): ?>
			<div class="row">
				<?php if ($item['rewatched'] == 1): ?>
					<div>Rewatched once</div>
				<?php elseif ($item['rewatched'] == 2): ?>
					<div>Rewatched twice</div>
				<?php elseif ($item['rewatched'] == 3): ?>
					<div>Rewatched thrice</div>
				<?php else: ?>
					<div>Rewatched <?= $item['rewatched'] ?> times</div>
				<?php endif ?>
			</div>
		<?php endif ?>

		<?php if (count($item['anime']['streaming_links']) > 0): ?>
			<div class="row">
				<?php foreach ($item['anime']['streaming_links'] as $link): ?>
					<div class="cover-streaming-link">
						<?php if ($link['meta']['link']): ?>
							<a href="<?= $link['link'] ?>"
							   title="Stream '<?= $item['anime']['title'] ?>' on <?= $link['meta']['name'] ?>">
								<?= $helper->img("/public/images/{$link['meta']['image']}", [
									'class' => 'streaming-logo',
									'width' => 20,
									'height' => 20,
									'alt' => "{$link['meta']['name']} logo",
								]); ?>
							</a>
						<?php else: ?>
							<?= $helper->img("/public/images/{$link['meta']['image']}", [
								'class' => 'streaming-logo',
								'width' => 20,
								'height' => 20,
								'alt' => "{$link['meta']['name']} logo",
							]); ?>
						<?php endif ?>
					</div>
				<?php endforeach ?>
			</div>
		<?php endif ?>

		<?php if ($auth->isAuthenticated()): ?>
			<div class="row">
				<span class="edit">
					<a class="bracketed" title="Edit information about this anime" href="<?=
					$url->generate('edit', [
						'controller' => 'anime',
						'id' => $item['id'],
						'status' => $item['watching_status']
					]);
					?>">Edit</a>
				</span>
			</div>
		<?php endif ?>

		<div class="row">
			<div class="user-rating">Rating: <?= $item['user_rating'] ?> / 10</div>
			<div class="completion">Episodes:
				<span class="completed_number"><?= $item['episodes']['watched'] ?></span> /
				<span class="total_number"><?= $item['episodes']['total'] ?></span>
			</div>
		</div>
		<div class="row">
			<div class="media_type"><?= $escape->html($item['anime']['show_type']) ?></div>
			<div class="airing-status"><?= $escape->html($item['airing']['status']) ?></div>
			<div class="age-rating"><?= $escape->html($item['anime']['age_rating']) ?></div>
		</div>
	</div>
</article>