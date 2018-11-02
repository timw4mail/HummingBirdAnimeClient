<?php
use function Aviat\AnimeClient\getLocalImg;
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="user-page details">
	<section class="flex flex-no-wrap">
		<div>
			<center>
				<?php
					$avatar = $urlGenerator->assetUrl(
						getLocalImg($attributes['avatar']['original'], FALSE)
					);
					echo $helper->img($avatar, ['alt' => '']);
				?>
			</center>
			<br />
			<br />
			<table class="media_details">
				<tr>
					<th colspan="2">General</th>
				</tr>
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
				<tr>
					<th colspan="2">User Stats</th>
				</tr>
				<tr>
					<td>Time spent watching anime:</td>
					<td><?= $timeOnAnime ?></td>
				</tr>
				<tr>
					<td># of Anime episodes watched</td>
					<td><?= $stats['anime-amount-consumed']['units'] ?></td>
				</tr>
				<tr>
					<td># of Manga chapters read</td>
					<td><?= $stats['manga-amount-consumed']['units'] ?></td>
				</tr>
				<tr>
					<td># of Posts</td>
					<td><?= $attributes['postsCount'] ?></td>
				</tr>
				<tr>
					<td># of Comments</td>
					<td><?= $attributes['commentsCount'] ?></td>
				</tr>
				<tr>
					<td># of Media Rated</td>
					<td><?= $attributes['ratingsCount'] ?></td>
				</tr>
			</table>
		</div>
		<div>
			<h2>
				<?= $helper->a(
					"https://kitsu.io/users/{$attributes['slug']}",
					$attributes['name'], [
						'title' => 'View profile on Kitsu'
					])
				?>
			</h2>

			<dl>
				<dt><h3>About:</h3></dt>
				<dd><?= $escape->html($attributes['about']) ?></dd>
			</dl>

			<?php if ( ! empty($favorites)): ?>
			<h3>Favorites</h3>
				<?php if ( ! empty($favorites['characters'])): ?>
					<h4>Characters</h4>
					<section class="media-wrap">
					<?php foreach($favorites['characters'] as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
						<article class="small_character">
							<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
							<div class="name"><?= $helper->a($link, $char['canonicalName']); ?></div>
							<a href="<?= $link ?>">
								<?= $helper->picture("images/characters/{$char['id']}.webp") ?>
							</a>
						</article>
						<?php endif ?>
					<?php endforeach ?>
					</section>
				<?php endif ?>
				<?php if ( ! empty($favorites['anime'])): ?>
					<h4>Anime</h4>
					<section class="media-wrap">
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
				<?php endif ?>
				<?php if ( ! empty($favorites['manga'])): ?>
					<h4>Manga</h4>
					<section class="media-wrap">
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
				<?php endif ?>
			<?php endif ?>
		</div>
	</section>
</main>