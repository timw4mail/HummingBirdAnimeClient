<?php use function Aviat\AnimeClient\getLocalImg; ?>
<main class="details fixed">
	<section class="flex">
		<aside class="info">
			<?= $helper->picture("images/anime/{$show_data['id']}-original.webp") ?>

			<br />

			<table class="media_details">
				<tr>
					<td class="align_right">Airing Status</td>
					<td><?= $show_data['status'] ?></td>
				</tr>
				<tr>
					<td>Show Type</td>
					<td><?= $show_data['show_type'] ?></td>
				</tr>
				<tr>
					<td>Episode Count</td>
					<td><?= $show_data['episode_count'] ?? '-' ?></td>
				</tr>
				<?php if ( ! empty($show_data['episode_length'])): ?>
					<tr>
						<td>Episode Length</td>
						<td><?= $show_data['episode_length'] ?> minutes</td>
					</tr>
				<?php endif ?>
				<?php if ( ! empty($show_data['age_rating'])): ?>
					<tr>
						<td>Age Rating</td>
						<td><abbr title="<?= $show_data['age_rating_guide'] ?>"><?= $show_data['age_rating'] ?></abbr>
						</td>
					</tr>
				<?php endif ?>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $show_data['genres']) ?>
					</td>
				</tr>
			</table>
		</aside>
		<article class="text">
			<h2 class="toph"><a rel="external" href="<?= $show_data['url'] ?>"><?= $show_data['title'] ?></a></h2>
			<?php foreach ($show_data['titles'] as $title): ?>
				<h3><?= $title ?></h3>
			<?php endforeach ?>
			<br />
			<p><?= nl2br($show_data['synopsis']) ?></p>
			<?php if (count($show_data['streaming_links']) > 0): ?>
				<hr />
				<h4>Streaming on:</h4>
				<table class="full_width invisible">
					<thead>
					<tr>
						<th class="align_left">Service</th>
						<th>Subtitles</th>
						<th>Dubs</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($show_data['streaming_links'] as $link): ?>
						<tr>
							<td class="align_left">
								<?php if ($link['meta']['link'] !== FALSE): ?>
									<a
										href="<?= $link['link'] ?>"
										title="Stream '<?= $show_data['title'] ?>' on <?= $link['meta']['name'] ?>"
									>
										<img
											class="streaming-logo" width="50" height="50"
											src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>"
											alt="<?= $link['meta']['name'] ?> logo"
										/>
										&nbsp;&nbsp;<?= $link['meta']['name'] ?>
									</a>
								<?php else: ?>
									<img
										class="streaming-logo" width="50" height="50"
										src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>"
										alt="<?= $link['meta']['name'] ?> logo"
									/>
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
			<?php if ( ! empty($show_data['trailer_id'])): ?>
				<hr />
				<h4>Trailer</h4>
				<iframe
					width="560" height="315" src="https://www.youtube.com/embed/<?= $show_data['trailer_id'] ?>"
					frameborder="0" allow="autoplay; encrypted-media" allowfullscreen
				></iframe>
			<?php endif ?>
		</article>
	</section>

	<?php if (count($characters) > 0): ?>
	<section>
		<h2>Characters</h2>

		<div class="tabs">
			<?php $i = 0 ?>
			<?php foreach ($characters as $role => $list): ?>
				<input
					type="radio" name="character-types"
					id="character-types-<?= $i ?>" <?= ($i === 0) ? 'checked' : '' ?> />
				<label for="character-types-<?= $i ?>"><?= ucfirst($role) ?></label>
				<section class="content media-wrap flex flex-wrap flex-justify-start">
					<?php foreach ($list as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
							<article class="<?= $role === 'supporting' ? 'small_' : '' ?>character">
								<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
								<div class="name">
									<?= $helper->a($link, $char['name']); ?>
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

	<?php if (count($staff) > 0): ?>
	<?php //dump($staff); ?>
	<section>
		<h2>Staff</h2>

		<div class="vertical-tabs">
			<?php $i = 0; ?>
			<?php foreach ($staff as $role => $people): ?>
				<div class="tab">
					<input type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="staff-role<?= $i ?>"><?= $role ?></label>
					<section class='content media-wrap flex flex-wrap flex-justify-start'>
						<?php foreach ($people as $pid => $person): ?>
							<article class='character small_person'>
								<?php $link = $url->generate('person', ['id' => $person['id']]) ?>
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