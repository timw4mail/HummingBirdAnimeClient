<?php

use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\Kitsu;

?>
<main class="character-page details fixed">
	<section class="flex flex-no-wrap">
		<aside>
			<?= $_->h->img($data['image']) ?>
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
				<p><?= nl2br($data['description']) ?></p>
			</div>
		</div>
	</section>

	<?php if ( ! (empty($data['media']['anime']) || empty($data['media']['manga']))): ?>
		<h3>Media</h3>

		<?= $_->component->tabs('character-media', $data['media'], static function ($media, $mediaType) use ($_) {
			$rendered = [];
			foreach ($media as $id => $item)
			{
				$rendered[] = $_->component->media(
					array_merge([$item['title']], $item['titles']),
					$_->urlFromRoute("{$mediaType}.details", ['id' => $item['slug']]),
					$_->h->img(Kitsu::getPosterImage($item), ['width' => 220, 'loading' => 'lazy']),
				);
			}

			return implode('', array_map('mb_trim', $rendered));
		}, 'media-wrap content') ?>
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
										$link = $_->urlFromRoute('person', ['id' => $c['person']['id']]);
										?>
										<a href="<?= $link ?>">
											<?= $_->h->img($c['person']['image']) ?>
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
												$link = $_->urlFromRoute('anime.details', ['id' => $series['attributes']['slug']]);
												$titles = Kitsu::filterTitles($series['attributes']);
												?>
												<a href="<?= $link ?>">
													<?= $_->h->img(Kitsu::getPosterImage($series['attributes'])) ?>
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

				<?= $_->component->tabs('character-vas', $vas, static function ($casting) use ($_) {
					$castings = [];
					foreach ($casting as $id => $c):
						$person = $_->component->character(
							$c['person']['name'],
							$_->urlFromRoute('person', ['slug' => $c['person']['slug']]),
							$_->h->img($c['person']['image']['original']['url']),
						);
						$medias = array_map(fn ($series) => $_->component->media(
							array_merge([$series['title']], $series['titles']),
							$_->urlFromRoute('anime.details', ['id' => $series['slug']]),
							$_->h->img(Kitsu::getPosterImage($series)),
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