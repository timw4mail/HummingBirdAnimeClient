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
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/anime/{$anime['id']}.webp") ?>
							</a>
							<div class="name">
								<a href="<?= $link ?>">
									<?= $anime['title'] ?>
									<?php foreach ($anime['titles'] as $title): ?>
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
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/manga/{$manga['id']}.webp") ?>
							</a>
							<div class="name">
								<a href="<?= $link ?>">
									<?= $manga['title'] ?>
									<?php foreach ($manga['titles'] as $title): ?>
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

				<?= $component->tabs('character-vas', $vas, static function ($casting) use ($url, $component, $helper) {
					$castings = [];
					foreach ($casting as $id => $c):
						$person = $component->character(
							$c['person']['name'],
							$url->generate('person', [
								'id' => $c['person']['id'],
								'slug' => $c['person']['slug']
							]),
							$helper->picture(getLocalImg($c['person']['image']))
						);
						$medias = array_map(fn ($series) => $component->media(
							array_merge([$series['title']], $series['titles']),
							$url->generate('anime.details', ['id' => $series['slug']]),
							$helper->picture(getLocalImg($series['posterImage'], TRUE))
						), $c['series']);
						$media = implode('', array_map('mb_trim', $medias));

						$castings[] = <<<HTML
							<tr>
								<td>{$person}</td>
								<td width="75%">
									<section class="align-left media-wrap-flex">
										{$media}
									</section>
								</td>
							</tr>
HTML;
					endforeach;

					$languages = implode('', array_map('mb_trim', $castings));

					return <<<HTML
						<table class="borderless max-table">
							<thead>
							<tr>
								<th>Cast Member</th>
								<th>Series</th>
							</tr>
							</thead>
							<tbody>{$languages}</tbody>
						</table>
HTML;
				}, 'content') ?>
			<?php endif ?>
		<?php endif ?>
	</section>
</main>