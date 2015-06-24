<main>
	<?php foreach ($sections as $name => $items): ?>
		<section class="status">
			<h2><?= $name ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<article class="media" id="a-<?= $item['anime']['id'] ?>">
					<?php if (is_logged_in()): ?>
					<button class="plus_one" hidden>+1 Episode</button>
					<?php endif ?>
					<img src="<?= $item['anime']['cover_image'] ?>" />
					<div class="name">
						<a href="<?= $item['anime']['url'] ?>">
						<?= $item['anime']['title'] ?>
						<?= ($item['anime']['alternate_title'] != "") ? "<br />({$item['anime']['alternate_title']})" : ""; ?>
						</a>
					</div>
					<div class="table">
						<div class="row">
							<div class="user_rating">Rating: <?= ($item['rating']['value'] > 0) ? (int)($item['rating']['value'] * 2) : " - " ?> / 10</div>
							<div class="completion">Episodes:
								<span class="completed_number"><?= $item['episodes_watched'] ?></span> /
								<span class="total_number"><?= ($item['anime']['episode_count'] != 0) ? $item['anime']['episode_count'] : "-" ?></span>
							</div>
						</div>
						<div class="row">
							<div class="media_type"><?= $item['anime']['show_type'] ?></div>
							<div class="airing_status"><?= $item['anime']['status'] ?></div>
							<div class="age_rating"><?= $item['anime']['age_rating'] ?></div>
						</div>
					</div>
				</article>
				<?php endforeach ?>
			</section>
		</section>
	<?php endforeach ?>
</main>
<?php if (is_logged_in()): ?>
<script src="<?= asset_url('js.php?g=edit') ?>"></script>
<?php endif ?>
