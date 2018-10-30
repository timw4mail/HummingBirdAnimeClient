<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<picture>
				<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$data[0]['id']}-original.webp") ?>" type="image/webp">
				<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$data[0]['id']}-original.jpg") ?>" type="image/jpeg">
				<img src="<?= $urlGenerator->assetUrl("images/characters/{$data[0]['id']}-original.jpg") ?>" alt="" />
			</picture>
			<?php if ( ! empty($data[0]['attributes']['otherNames'])): ?>
				<h3>Nicknames / Other names</h3>
				<?php foreach ($data[0]['attributes']['otherNames'] as $name): ?>
					<h4><?= $name ?></h4>
				<?php endforeach ?>
			<?php endif ?>
		</div>
		<div>
			<h2><?= $data['name'] ?></h2>
			<?php foreach ($data['names'] as $name): ?>
				<h3><?= $name ?></h3>
			<?php endforeach ?>

			<hr />

			<p class="description"><?= $data[0]['attributes']['description'] ?></p>
		</div>
	</section>

	<?php if (array_key_exists('anime', $data['included']) || array_key_exists('manga', $data['included'])): ?>
	<h3>Media</h3>
	<div class="tabs">
		<?php if (array_key_exists('anime', $data['included'])): ?>
		<input checked="checked" type="radio" id="media-anime" name="media-tabs" />
		<label for="media-anime"><h4> Anime </h4></label>

		<section class="align_left media-wrap content">
			<?php foreach($data['included']['anime'] as $id => $anime): ?>
			<article class="media">
				<?php
					$link = $url->generate('anime.details', ['id' => $anime['attributes']['slug']]);
					$titles = Kitsu::filterTitles($anime['attributes']);
				?>
				<a href="<?= $link ?>">
					<picture>
						<source
							srcset="<?= $urlGenerator->assetUrl("images/anime/{$id}.webp") ?>"
							type="image/webp"
						>
						<source
							srcset="<?= $urlGenerator->assetUrl("images/anime/{$id}.jpg") ?>"
							type="image/jpeg"
						>
						<img
							src="<?= $urlGenerator->assetUrl("images/anime/{$id}.jpg") ?>"
							alt=""
						/>
					</picture>
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
		<?php endif ?>

		<?php if (array_key_exists('manga', $data['included'])): ?>
		<input type="radio" id="media-manga" name="media-tabs" />
		<label for="media-manga"><h4>Manga</h4></label>

		<section class="align_left media-wrap content">

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
		<?php endif ?>
	</div>
	<?php endif ?>

	<section>
		<?php if ($castCount > 0): ?>
		<h3>Castings</h3>
			<?php
			$vas = $castings['Voice Actor'];
			unset($castings['Voice Actor']);
			ksort($vas)
			?>

			<?php if ( ! empty($vas)): ?>
				<h4>Voice Actors</h4>

				<div class="tabs">
					<?php $i = 0; ?>

					<?php foreach($vas as $language => $casting): ?>
						<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="character-va<?= $i ?>"
							name="character-vas"
						/>
						<label for="character-va<?= $i ?>"><h5><?= $language ?></h5></label>
						<section class="content">
							<table style='width:100%'>
								<tr>
									<th>Cast Member</th>
									<th>Series</th>
								</tr>
								<?php foreach($casting as $cid => $c): ?>
									<tr>
										<td style="width:229px">
											<article class="character">
												<?php
												$link = $url->generate('person', ['id' => $c['person']['id']]);
												?>
												<a href="<?= $link ?>">
													<img
														src="<?= $urlGenerator->assetUrl(getLocalImg($c['person']['image'])) ?>"
														alt=""
													/>
													<div class="name">
														<?= $c['person']['name'] ?>
													</div>
												</a>
											</article>
										</td>
										<td>
											<section class="align_left media-wrap-flex">
												<?php foreach ($c['series'] as $series): ?>
													<article class="media">
														<?php
														$link = $url->generate('anime.details', ['id' => $series['attributes']['slug']]);
														$titles = Kitsu::filterTitles($series['attributes']);
														?>
														<a href="<?= $link ?>">
															<img
																src="<?= $urlGenerator->assetUrl(getLocalImg($series['attributes']['posterImage']['small'])) ?>"
																width="220" alt=""
															/>
														</a>
														<div class="name">
															<a href="<?= $link ?>">
																<?= array_shift($titles) ?>
																<?php foreach ($titles as $title): ?>
																	<br />
																	<small><?= $title ?></small>
																<?php endforeach ?>
															</a>
														</div>
													</article>
												<?php endforeach ?>
											</section>
										</td>
									</tr>
								<?php endforeach ?>
							</table>
						</section>
						<?php $i++ ?>
					<?php endforeach ?>
				</div>
			<?php endif ?>


			<?php foreach($castings as $role => $entries): ?>
				<h4><?= $role ?></h4>
				<?php foreach($entries as $language => $casting): ?>
					<h5><?= $language ?></h5>
					<table class="min-table">
					<tr>
						<th>Cast Member</th>
						<th>Series</th>
					</tr>
					<?php foreach($casting as $cid => $c):?>
					<tr>
						<td style="width:229px">
							<article class="character">
								<?php
									$link = $url->generate('person', ['id' => $c['person']['id']]);
								?>
								<a href="<?= $link ?>">
								<img src="<?= $urlGenerator->assetUrl(getLocalImg($c['person']['image'])) ?>" alt="" />
								<div class="name">
									<?= $c['person']['name'] ?>
								</div>
								</a>
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
									<img src="<?= $urlGenerator->assetUrl(getLocalImg($series['attributes']['posterImage']['small'])) ?>" width="220" alt="" />
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