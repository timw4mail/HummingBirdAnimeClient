<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<h2><?= $attributes['name'] ?></h2>
			<img src="<?= $attributes['avatar']['original'] ?>" alt="" />
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
				<dd><?= $escape->html($attributes['bio']) ?></dd>
			</dl>
			<?php if ( ! empty($favorites)): ?>
			<h3>Favorites:</h3>
				<?php if ( ! empty($favorites['characters'])): ?>
					<section>
					<h4>Characters</h4>
					<div class="flex flex-wrap">
					<?php foreach($favorites['characters'] as $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
						<div class="small_character">
							<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
							<?= $helper->a($link, $char['name']); ?>
							<br />
							<a href="<?= $link ?>">
							<?= $helper->img($char['image']['original'], [
								'width' => '225'
							]) ?>
							</a>
						</div>
						<?php endif ?>
					<?php endforeach ?>
					</div>
					</section>
				<?php endif ?>
			<?php endif ?>
		</div>
	</section>
</main>