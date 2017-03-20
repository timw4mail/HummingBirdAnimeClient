<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<h2><?= $attributes['name'] ?></h2>
			<img src="<?= $attributes['avatar']['original'] ?>" alt="" />
			<br />
			<br />
			<table class="media_details">
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
			</table>
		</div>
		<div>
			<dl>
				<dt>About:</dt>
				<dd><?= $escape->html($attributes['bio']) ?></dd>
			</dl>
			<?php /* <pre><?= json_encode($attributes, \JSON_PRETTY_PRINT) ?></pre>
			<pre><?= json_encode($relationships, \JSON_PRETTY_PRINT) ?></pre>
			<pre><?= json_encode($included, \JSON_PRETTY_PRINT) ?></pre> */ ?>
		</div>
	</section>
</main>