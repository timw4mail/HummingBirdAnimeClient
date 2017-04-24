<?php use Aviat\AnimeClient\API\Kitsu; ?>
<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" width="284" src="<?= $urlGenerator->assetUrl("images/characters/{$data[0]['id']}.jpg") ?>" alt="" />
		</div>
		<div>
			<h2><?= $data[0]['attributes']['name'] ?></h2>

			<p class="description"><?= $data[0]['attributes']['description'] ?></p>
		</div>
	</section>

	<?php if (array_key_exists('anime', $data['included']) || array_key_exists('manga', $data['included'])): ?>
	<h3>Media</h3>
	<section class="flex flex-no-wrap">
		<?php if (array_key_exists('anime', $data['included'])): ?>
		<div>
			<h4>Anime</h4>
			<section class="align_left media-wrap">
				<?php foreach($data['included']['anime'] as $id => $anime): ?>
				<article class="media">
					<?php
						$link = $url->generate('anime.details', ['id' => $anime['attributes']['slug']]);
						$titles = Kitsu::filterTitles($anime['attributes']);
					?>
					<a href="<?= $link ?>">
						<img src="<?= $urlGenerator->assetUrl("images/anime/{$id}.jpg") ?>" width="220" alt="" />
					</a>
					<div class="name">
						<a href="<?= $link ?>">
							<?= array_shift($titles) ?>
							<?php foreach ($titles as $title): ?>
								<br /><small><?= $title ?></small>
							<?php endforeach ?>
						</a>
					</div>
				</article>
				<?php endforeach ?>
			</section>
		</div>
		<?php endif ?>
		<?php if (array_key_exists('manga', $data['included'])): ?>
		<div>
			<h4>Manga</h4>
			<section class="align_left media-wrap">

				<?php foreach($data['included']['manga'] as $id => $manga): ?>
				<article class="media">
					<?php
						$link = $url->generate('manga.details', ['id' => $manga['attributes']['slug']]);
						$titles = Kitsu::filterTitles($manga['attributes']);
					?>
					<a href="<?= $link ?>">
						<img src="<?= $urlGenerator->assetUrl("images/manga/{$id}.jpg") ?>" width="220" alt="" />
					</a>
					<div class="name">
						<a href="<?= $link ?>">
							<?= array_shift($titles) ?>
							<?php foreach ($titles as $title): ?>
								<br /><small><?= $title ?></small>
							<?php endforeach ?>
						</a>
					</div>
				</article>
				<?php endforeach ?>

			</section>
		</div>
		<?php endif ?>
	</section>
	<?php endif ?>

	<section>
		<?php if ($castCount > 0): ?>
		<h3>Castings</h3>
			<?php foreach($castings as $role => $entries): ?>
				<h4><?= $role ?></h4>
				<?php foreach($entries as $language => $casting): ?>
					<h5><?= $language ?></h5>
					<table class="min-table">
					<tr>
						<th>Cast Member</th>
						<th>Series</th>
					</tr>
					<?php foreach($casting as $c):?>
					<tr>
						<td style="width:229px">
							<article class="character">
								<img src="<?= $c['person']['image'] ?>" alt="" />
								<div class="name">
									<?= $c['person']['name'] ?>
								</div>
							</article>
						</td>
						<td>
							<section class="align_left media-wrap">
							<?php foreach($c['series'] as $series): ?>
								<article class="media">
								<?php
									$link = $url->generate('anime.details', ['id' => $series['attributes']['slug']]);
									$titles = Kitsu::filterTitles($series['attributes']);
								?>
								<a href="<?= $link ?>">
									<img src="<?= $series['attributes']['posterImage']['small'] ?>" width="220" alt="" />
								</a>
								<div class="name">
									<a href="<?= $link ?>">
										<?= array_shift($titles) ?>
										<?php foreach ($titles as $title): ?>
											<br /><small><?= $title ?></small>
										<?php endforeach ?>
									</a>
								</div>
								</article>
							<?php endforeach ?>
							</section>
						</td>
					</tr>
					<?php endforeach; ?>
					</table>
				<?php endforeach ?>
			<?php endforeach ?>
		<?php endif ?>
	</section>
</main>