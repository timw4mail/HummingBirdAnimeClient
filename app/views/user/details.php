<?php
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="user-page details">
	<h2 class="toph">
		<?= $helper->a(
			"https://kitsu.io/users/{$data['slug']}",
			$data['name'], [
			'title' => 'View profile on Kitsu'
		])
		?>
	</h2>

	<p><?= $escape->html($data['about']) ?></p>

	<section class="flex flex-no-wrap">
		<aside class="info">
			<center>
				<?= $helper->img($urlGenerator->assetUrl($data['avatar']), ['alt' => '']); ?>
			</center>
			<br />
			<table class="media-details">
				<tr>
					<td>Location</td>
					<td><?= $data['location'] ?></td>
				</tr>
				<tr>
					<td>Website</td>
					<td><?= $helper->a($data['website'], $data['website']) ?></td>
				</tr>
				<?php if ( ! empty($data['waifu'])): ?>
				<tr>
					<td><?= $escape->html($data['waifu']['label']) ?></td>
					<td>
						<?php
							$character = $data['waifu']['character'];
							echo $helper->a(
								$url->generate('character', ['slug' => $character['slug']]),
								$character['canonicalName']
							);
						?>
					</td>
				</tr>
				<?php endif ?>
			</table>

			<h3>User Stats</h3><br />
			<table class="media-details">
				<?php foreach($data['stats'] as $label => $stat): ?>
				<tr>
					<td><?= $label ?></td>
					<td><?= $stat ?></td>
				</tr>
				<?php endforeach ?>
			</table>
		</aside>
		<article>
			<?php if ( ! empty($data['favorites'])): ?>
			<h3>Favorites</h3>
			<div class="tabs">
				<?php $i = 0 ?>
				<?php if ( ! empty($data['favorites']['characters'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-chars" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-chars">Characters</label>
					<section class="content full-width media-wrap">
					<?php foreach($data['favorites']['characters'] as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
						<article class="character">
							<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
							<div class="name"><?= $helper->a($link, $char['canonicalName']); ?></div>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/characters/{$char['id']}.webp") ?>
							</a>
						</article>
						<?php endif ?>
					<?php endforeach ?>
					</section>
					<?php $i++; ?>
				<?php endif ?>
				<?php if ( ! empty($data['favorites']['anime'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-anime" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-anime">Anime</label>
					<section class="content full-width media-wrap">
						<?php foreach($data['favorites']['anime'] as $anime): ?>
						<article class="media">
							<?php
								$link = $url->generate('anime.details', ['id' => $anime['slug']]);
								$titles = Kitsu::filterTitles($anime);
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/anime/{$anime['id']}.webp") ?>
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
					<?php $i++; ?>
				<?php endif ?>
				<?php if ( ! empty($data['favorites']['manga'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-manga" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-manga">Manga</label>
					<section class="content full-width media-wrap">
						<?php foreach($data['favorites']['manga'] as $manga): ?>
						<article class="media">
							<?php
								$link = $url->generate('manga.details', ['id' => $manga['slug']]);
								$titles = Kitsu::filterTitles($manga);
							?>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/manga/{$manga['id']}.webp") ?>
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
					<?php $i++; ?>
				<?php endif ?>
			</div>
			<?php endif ?>
		</article>
	</section>
</main>