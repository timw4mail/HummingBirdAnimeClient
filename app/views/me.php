<?php use Aviat\AnimeClient\API\Kitsu; ?>
<main class="user-page details">
	<section class="flex flex-no-wrap">
		<div>
			<center>
				<h2>
					<a title='View profile on Kisu'
						href="https://kitsu.io/users/<?= $attributes['name'] ?>">
						<?= $attributes['name'] ?>
					</a>
				</h2>
				<?php
					$file = basename(parse_url($attributes['avatar']['original'], \PHP_URL_PATH));
					$parts = explode('.', $file);
					$ext = end($parts);
				?>
				<img src="<?= $urlGenerator->assetUrl('images/avatars', "{$data['id']}.{$ext}") ?>" alt="" />
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
								$character['name']
							);
						?>
					</td>
				</tr>
				<?php endif ?>
				<tr>
					<th colspan="2">User Stats</th>
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
			<dl>
				<dt>About:</dt>
				<dd><?= $escape->html($attributes['about']) ?></dd>
			</dl>
			<?php if ( ! empty($favorites)): ?>
				<?php if ( ! empty($favorites['characters'])): ?>
					<h4>Favorite Characters</h4>
					<section class="media-wrap">
					<?php foreach($favorites['characters'] as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
						<article class="small_character">
							<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
							<div class="name"><?= $helper->a($link, $char['name']); ?></div>
							<a href="<?= $link ?>">
								<picture>
									<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$char['id']}.webp") ?>" type="image/webp">
									<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$char['id']}.jpg") ?>" type="image/jpeg">
									<img src="<?= $urlGenerator->assetUrl("images/characters/{$char['id']}.jpg") ?>" alt="" />
								</picture>
							</a>
						</article>
						<?php endif ?>
					<?php endforeach ?>
					</section>
				<?php endif ?>
				<?php if ( ! empty($favorites['anime'])): ?>
					<h4>Favorite Anime</h4>
					<section class="media-wrap">
						<?php foreach($favorites['anime'] as $anime): ?>
						<article class="media">
							<?php
								$link = $url->generate('anime.details', ['id' => $anime['slug']]);
								$titles = Kitsu::filterTitles($anime);
							?>
							<a href="<?= $link ?>">
								<picture width="220">
									<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$anime['id']}.webp") ?>" type="image/webp">
									<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$anime['id']}.jpg") ?>" type="image/jpeg">
									<img src="<?= $urlGenerator->assetUrl("images/anime/{$anime['id']}.jpg") ?>" width="220" alt="" />
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
				<?php if ( ! empty($favorites['manga'])): ?>
					<h4>Favorite Manga</h4>
					<section class="media-wrap">
						<?php foreach($favorites['manga'] as $manga): ?>
						<article class="media">
							<?php
								$link = $url->generate('manga.details', ['id' => $manga['slug']]);
								$titles = Kitsu::filterTitles($manga);
							?>
							<a href="<?= $link ?>">
								<picture width="220">
									<source srcset="<?= $urlGenerator->assetUrl("images/manga/{$manga['id']}.webp") ?>" type="image/webp">
									<source srcset="<?= $urlGenerator->assetUrl("images/manga/{$manga['id']}.jpg") ?>" type="image/jpeg">
									<img src="<?= $urlGenerator->assetUrl("images/manga/{$manga['id']}.jpg") ?>" width="220" alt="" />
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
			<?php endif ?>
		</div>
	</section>
</main>