<article
	class="media"
 	data-kitsu-id="<?= $item['id'] ?>"
 	data-mal-id="<?= $item['mal_id'] ?>"
>
	<?php if ($auth->isAuthenticated()): ?>
		<button title="Increment episode count" class="plus_one" hidden>+1 Episode</button>
	<?php endif ?>
	<picture>
		<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$item['anime']['id']}.webp") ?>" type="image/webp">
		<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$item['anime']['id']}.jpg") ?>" type="image/jpeg">
		<img src="<?= $urlGenerator->assetUrl("images/anime/{$item['anime']['id']}.jpg") ?>" alt="" />
	</picture>

	<div class="name">
		<a href="<?= $url->generate('anime.details', ['id' => $item['anime']['slug']]); ?>">
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
				<div>Rewatched <?= $item['rewatched'] ?> time(s)</div>
			</div>
		<?php endif ?>

		<?php if (count($item['anime']['streaming_links']) > 0): ?>
			<div class="row">
				<?php foreach ($item['anime']['streaming_links'] as $link): ?>
					<div class="cover_streaming_link">
						<?php if ($link['meta']['link']): ?>
							<a href="<?= $link['link'] ?>"
							   title="Stream '<?= $item['anime']['title'] ?>' on <?= $link['meta']['name'] ?>">
								<img class="streaming-logo" width="20" height="20"
									 src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>"
									 alt="<?= $link['meta']['name'] ?> logo"/>
							</a>
						<?php else: ?>
							<img class="streaming-logo" width="20" height="20"
								 src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>"
								 alt="<?= $link['meta']['name'] ?> logo"/>
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
			<div class="user_rating">Rating: <?= $item['user_rating'] ?> / 10</div>
			<div class="completion">Episodes:
				<span class="completed_number"><?= $item['episodes']['watched'] ?></span> /
				<span class="total_number"><?= $item['episodes']['total'] ?></span>
			</div>
		</div>
		<div class="row">
			<div class="media_type"><?= $escape->html($item['anime']['show_type']) ?></div>
			<div class="airing_status"><?= $escape->html($item['airing']['status']) ?></div>
			<div class="age_rating"><?= $escape->html($item['anime']['age_rating']) ?></div>
		</div>
	</div>
</article>