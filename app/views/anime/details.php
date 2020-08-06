<?php
use Aviat\AnimeClient\API\Kitsu;
use function Aviat\AnimeClient\getLocalImg;
?>
<main class="details fixed">
	<section class="flex">
		<aside class="info">
			<?= $helper->picture("images/anime/{$data['id']}-original.webp") ?>

			<br />

			<table class="media-details">
				<tr>
					<td class="align-right">Airing Status</td>
					<td><?= $data['status'] ?></td>
				</tr>

				<tr>
					<td>Show Type</td>
					<td><?= (strlen($data['show_type']) > 3) ? ucfirst(strtolower($data['show_type'])) : $data['show_type'] ?></td>
				</tr>

				<?php if ($data['episode_count'] !== 1): ?>
					<tr>
						<td>Episode Count</td>
						<td><?= $data['episode_count'] ?? '-' ?></td>
					</tr>
				<?php endif ?>

				<?php if (( ! empty($data['episode_length'])) && $data['episode_count'] !== 1): ?>
					<tr>
						<td>Episode Length</td>
						<td><?= Kitsu::friendlyTime($data['episode_length']) ?></td>
					</tr>
				<?php endif ?>

				<?php if (isset($data['total_length'], $data['episode_count']) && ! empty($data['total_length'])): ?>
					<tr>
						<td>Total Length</td>
						<td><?= Kitsu::friendlyTime($data['total_length']) ?></td>
					</tr>
				<?php endif ?>

				<?php if ( ! empty($data['age_rating'])): ?>
					<tr>
						<td>Age Rating</td>
						<td><abbr title="<?= $data['age_rating_guide'] ?>"><?= $data['age_rating'] ?></abbr>
						</td>
					</tr>
				<?php endif ?>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $data['genres']) ?>
					</td>
				</tr>
			</table>

			<br />

		</aside>
		<article class="text">
			<h2 class="toph"><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php foreach ($data['titles_more'] as $title): ?>
				<h3><?= $title ?></h3>
			<?php endforeach ?>
			<br />
			<div class="description">
				<p><?= str_replace("\n", '</p><p>', $data['synopsis']) ?></p>
			</div>
			<?php if (count($data['streaming_links']) > 0): ?>
				<hr />
				<h4>Streaming on:</h4>
				<table class="full-width invisible streaming-links">
					<thead>
					<tr>
						<th class="align-left">Service</th>
						<th>Subtitles</th>
						<th>Dubs</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($data['streaming_links'] as $link): ?>
						<tr>
							<td class="align-left">
								<?php if ($link['meta']['link'] !== FALSE): ?>
									<a
										href="<?= $link['link'] ?>"
										title="Stream '<?= $data['title'] ?>' on <?= $link['meta']['name'] ?>"
									>
										<?= $helper->img("/public/images/{$link['meta']['image']}", [
											'class' => 'streaming-logo',
											'width' => 50,
											'height' => 50,
											'alt' => "{$link['meta']['name']} logo",
										]) ?>
										&nbsp;&nbsp;<?= $link['meta']['name'] ?>
									</a>
								<?php else: ?>
									<?= $helper->img("/public/images/{$link['meta']['image']}", [
										'class' => 'streaming-logo',
										'width' => 50,
										'height' => 50,
										'alt' => "{$link['meta']['name']} logo",
									]) ?>
									&nbsp;&nbsp;<?= $link['meta']['name'] ?>
								<?php endif ?>
							</td>
							<td><?= implode(', ', $link['subs']) ?></td>
							<td><?= implode(', ', $link['dubs']) ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php endif ?>
			<?php if ( ! empty($data['trailer_id'])): ?>
				<div class="responsive-iframe">
				<h4>Trailer</h4>
				<iframe
					width="560"
					height="315"
					src="https://www.youtube.com/embed/<?= $data['trailer_id'] ?>"
					frameborder="0"
					allow="autoplay; encrypted-media"
					allowfullscreen
				></iframe>
				</div>
			<?php endif ?>
		</article>
	</section>

	<?php if (count($data['characters']) > 0): ?>
	<section>
		<h2>Characters</h2>

		<div class="tabs">
			<?php $i = 0 ?>
			<?php foreach ($data['characters'] as $role => $list): ?>
				<input
					type="radio" name="character-types"
					id="character-types-<?= $i ?>" <?= ($i === 0) ? 'checked' : '' ?> />
				<label for="character-types-<?= $i ?>"><?= ucfirst($role) ?></label>
				<section class="content media-wrap flex flex-wrap flex-justify-start">
					<?php foreach ($list as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
							<article class="<?= $role === 'supporting' ? 'small-' : '' ?>character">
								<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
								<div class="name">
									<?= $helper->a($link, $char['name']) ?>
								</div>
								<a href="<?= $link ?>">
									<?= $helper->picture("images/characters/{$id}.webp") ?>
								</a>
							</article>
						<?php endif ?>
					<?php endforeach ?>
				</section>
				<?php $i++; ?>
			<?php endforeach ?>
		</div>
	</section>
	<?php endif ?>

	<?php if (count($data['staff']) > 0): ?>
	<section>
		<h2>Staff</h2>

		<div class="vertical-tabs">
			<?php $i = 0; ?>
			<?php foreach ($data['staff'] as $role => $people): ?>
				<div class="tab">
					<input type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="staff-role<?= $i ?>"><?= $role ?></label>
					<section class='content media-wrap flex flex-wrap flex-justify-start'>
						<?php foreach ($people as $pid => $person): ?>
							<article class='character small-person'>
								<?php $link = $url->generate('person', ['id' => $person['id'], 'slug' => $person['slug']]) ?>
								<div class="name">
									<a href="<?= $link ?>">
										<?= $person['name'] ?>
									</a>
								</div>
								<a href="<?= $link ?>">
									<?= $helper->picture(getLocalImg($person['image']['original'] ?? NULL)) ?>
								</a>
							</article>
						<?php endforeach ?>
					</section>
				</div>
				<?php $i++; ?>
			<?php endforeach ?>
		</div>
	</section>
	<?php endif ?>
</main>