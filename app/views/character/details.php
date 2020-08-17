<?php

use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;

?>
<main class="character-page details fixed">
	<section class="flex flex-no-wrap">
		<aside>
			<?= $helper->picture("images/characters/{$data['id']}-original.webp") ?>
		</aside>
		<div>
			<h2 class="toph"><?= $data['name'] ?></h2>
			<?php foreach ($data['names'] as $name): ?>
				<h3><?= $name ?></h3>
			<?php endforeach ?>

			<?php if ( ! empty($data['otherNames'])): ?>
				<h4>Also Known As:</h4>
				<ul>
				<?php foreach ($data['otherNames'] as $name): ?>
					<li><h5><?= $name ?></h5></li>
				<?php endforeach ?>
				</ul>
			<?php endif ?>
			<br />
			<hr />
			<div class="description">
				<p><?= str_replace("\n", '</p><p>', $data['description']) ?></p>
			</div>
		</div>
	</section>

	<?php if ( ! (empty($data['media']['anime']) || empty($data['media']['manga']))): ?>
		<h3>Media</h3>
		<div class="tabs">
			<?php if ( ! empty($data['media']['anime'])): ?>
				<input checked="checked" type="radio" id="media-anime" name="media-tabs" />
				<label for="media-anime">Anime</label>

				<section class="media-wrap content">
					<?php foreach ($data['media']['anime'] as $id => $anime): ?>
						<article class="media">
							<?php
							$link = $url->generate('anime.details', ['id' => $anime['slug']]);
							$titles = Kitsu::getTitles($anime['titles']);
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/anime/{$anime['id']}.webp") ?>
							</a>
							<div class="name">
								<a href="<?= $link ?>">
									<?= $anime['titles']['canonical'] ?>
									<?php foreach ($titles as $title): ?>
										<br />
										<small><?= $title ?></small>
									<?php endforeach ?>
								</a>
							</div>
						</article>
					<?php endforeach ?>
				</section>
			<?php endif ?>

			<?php if ( ! empty($data['media']['manga'])): ?>
				<input type="radio" id="media-manga" name="media-tabs" />
				<label for="media-manga">Manga</label>

				<section class="media-wrap content">
					<?php foreach ($data['media']['manga'] as $id => $manga): ?>
						<article class="media">
							<?php
							$link = $url->generate('manga.details', ['id' => $manga['slug']]);
							$titles = Kitsu::getTitles($manga['titles']);
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/manga/{$manga['id']}.webp") ?>
							</a>
							<div class="name">
								<a href="<?= $link ?>">
									<?= $manga['titles']['canonical'] ?>
									<?php foreach ($titles as $title): ?>
										<br />
										<small><?= $title ?></small>
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
		<?php if (count($data['castings']) > 0): ?>
			<h3>Castings</h3>
			<?php
				$vas = $data['castings']['Voice Actor'];
				unset($data['castings']['Voice Actor']);
				ksort($vas)
			?>

			<?php foreach ($data['castings'] as $role => $entries): ?>
				<h4><?= $role ?></h4>
				<?php foreach ($entries as $language => $casting): ?>
					<h5><?= $language ?></h5>
					<table class="min-table">
						<tr>
							<th>Cast Member</th>
							<th>Series</th>
						</tr>
						<?php foreach ($casting as $cid => $c): ?>
							<tr>
								<td>
									<article class="character">
										<?php
										$link = $url->generate('person', ['id' => $c['person']['id']]);
										?>
										<a href="<?= $link ?>">
											<?= $helper->picture(getLocalImg($c['person']['image'], TRUE)) ?>
											<div class="name">
												<?= $c['person']['name'] ?>
											</div>
										</a>
									</article>
								</td>
								<td>
									<section class="align-left media-wrap">
										<?php foreach ($c['series'] as $series): ?>
											<article class="media">
												<?php
												$link = $url->generate('anime.details', ['id' => $series['attributes']['slug']]);
												$titles = Kitsu::filterTitles($series['attributes']);
												?>
												<a href="<?= $link ?>">
													<?= $helper->picture(getLocalImg($series['attributes']['posterImage']['small'], TRUE)) ?>
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
						<?php endforeach; ?>
					</table>
				<?php endforeach ?>
			<?php endforeach ?>

			<?php if ( ! empty($vas)): ?>
				<h4>Voice Actors</h4>

				<div class="tabs">
					<?php $i = 0; ?>

					<?php foreach ($vas as $language => $casting): ?>
						<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="character-va<?= $i ?>"
							name="character-vas"
						/>
						<label for="character-va<?= $i ?>"><?= $language ?></label>
						<section class="content">
							<table class="borderless max-table">
								<tr>
									<th>Cast Member</th>
									<th>Series</th>
								</tr>
								<?php foreach ($casting as $cid => $c): ?>
									<tr>
										<td>
											<article class="character">
												<?php
												$link = $url->generate('person', ['id' => $c['person']['id']]);
												?>
												<a href="<?= $link ?>">
													<?= $helper->picture(getLocalImg($c['person']['image'])) ?>
													<div class="name">
														<?= $c['person']['name'] ?>
													</div>
												</a>
											</article>
										</td>
										<td width="75%">
											<section class="align-left media-wrap-flex">
												<?php foreach ($c['series'] as $series): ?>
													<article class="media">
														<?php
														$link = $url->generate('anime.details', ['id' => $series['attributes']['slug']]);
														$titles = Kitsu::filterTitles($series['attributes']);
														?>
														<a href="<?= $link ?>">
															<?= $helper->picture(getLocalImg($series['attributes']['posterImage']['small'], TRUE)) ?>
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
		<?php endif ?>
	</section>
</main>