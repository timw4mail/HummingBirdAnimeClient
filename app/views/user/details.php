<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="user-page details">
	<h2 class="toph">
		<?= $helper->a(
			"https://kitsu.io/users/{$attributes['slug']}",
			$attributes['name'], [
			'title' => 'View profile on Kitsu'
		])
		?>
	</h2>

	<p><?= $escape->html($attributes['about']) ?></p>

	<section class="flex flex-no-wrap">
		<aside class="info">
			<center>
				<?php
					$avatar = $urlGenerator->assetUrl(
						getLocalImg($attributes['avatar']['original'], FALSE)
					);
					echo $helper->img($avatar, ['alt' => '']);
				?>
			</center>
			<br />
			<table class="media-details">
				<tr>
					<td>Location</td>
					<td><?= $attributes['location'] ?></td>
				</tr>
				<tr>
					<td>Website</td>
					<td><?= $helper->a($attributes['website'], $attributes['website']) ?></td>
				</tr>
				<?php if (array_key_exists('waifu', $relationships)): ?>
				<tr>
					<td><?= $escape->html($attributes['waifuOrHusbando']) ?></td>
					<td>
						<?php
							$character = $relationships['waifu']['attributes'];
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
				<tr>
					<td>Time spent watching anime:</td>
					<td><?= $timeOnAnime ?></td>
				</tr>
				<tr>
					<td># of Anime episodes watched</td>
					<td><?= number_format($stats['anime-amount-consumed']['units']) ?></td>
				</tr>
				<tr>
					<td># of Manga chapters read</td>
					<td><?= number_format($stats['manga-amount-consumed']['units']) ?></td>
				</tr>
				<tr>
					<td># of Posts</td>
					<td><?= number_format($attributes['postsCount']) ?></td>
				</tr>
				<tr>
					<td># of Comments</td>
					<td><?= number_format($attributes['commentsCount']) ?></td>
				</tr>
				<tr>
					<td># of Media Rated</td>
					<td><?= number_format($attributes['ratingsCount']) ?></td>
				</tr>
			</table>
		</aside>
		<article>
			<?php if ( ! empty($favorites)): ?>
			<h3>Favorites</h3>
			<div class="tabs">
				<?php $i = 0 ?>
				<?php if ( ! empty($favorites['characters'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-chars" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-chars">Characters</label>
					<section class="content full-width media-wrap">
					<?php foreach($favorites['characters'] as $id => $char): ?>
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
				<?php if ( ! empty($favorites['anime'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-anime" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-anime">Anime</label>
					<section class="content full-width media-wrap">
						<?php foreach($favorites['anime'] as $anime): ?>
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
				<?php if ( ! empty($favorites['manga'])): ?>
					<input type="radio" name="user-favorites" id="user-fav-manga" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="user-fav-manga">Manga</label>
					<section class="content full-width media-wrap">
						<?php foreach($favorites['manga'] as $manga): ?>
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