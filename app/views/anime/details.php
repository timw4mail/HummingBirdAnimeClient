<main class="details fixed">
	<section class="flex">
		<aside class="info">
			<picture class="cover">
				<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$show_data['id']}-original.webp") ?>" type="image/webp">
				<source srcset="<?= $urlGenerator->assetUrl("images/anime/{$show_data['id']}-original.jpg") ?>" type="image/jpeg">
				<img src="<?= $urlGenerator->assetUrl("images/anime/{$show_data['id']}-original.jpg") ?>" alt="" />
			</picture>
			<br />
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
                    <td><abbr title="<?= $show_data['age_rating_guide'] ?>"><?= $show_data['age_rating'] ?></abbr></td>
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
			<h2><a rel="external" href="<?= $show_data['url'] ?>"><?= $show_data['title'] ?></a></h2>
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
				<?php foreach($show_data['streaming_links'] as $link): ?>
					<tr>
						<td class="align_left">
							<?php if ($link['meta']['link'] !== FALSE): ?>
							<a href="<?= $link['link'] ?>" title="Stream '<?= $show_data['title'] ?>' on <?= $link['meta']['name'] ?>">
								<img class="streaming-logo" width="50" height="50" src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>" alt="<?= $link['meta']['name'] ?> logo" />
								&nbsp;&nbsp;<?= $link['meta']['name'] ?>
							</a>
							<?php else: ?>
								<img class="streaming-logo" width="50" height="50" src="<?= $urlGenerator->assetUrl('images', $link['meta']['image']) ?>" alt="<?= $link['meta']['name'] ?> logo" />
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
				<iframe width="560" height="315" src="https://www.youtube.com/embed/<?= $show_data['trailer_id'] ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
			<?php endif ?>
		</article>
	</section>

	<?php if (count($characters) > 0): ?>
	<br />
	<hr />
	<h2>Characters</h2>
	<?php foreach($characters as $role => $list): ?>
	<h3><?= ucfirst($role) ?></h3>
	<section class="media-wrap flex flex-wrap flex-justify-start">
	<?php foreach($list as $id => $char): ?>
		<?php if ( ! empty($char['image']['original'])): ?>
		<article class="character">
			<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
			<div class="name">
				<?= $helper->a($link, $char['name']); ?>
			</div>
			<a href="<?= $link ?>">
				<picture>
					<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$id}.webp") ?>" type="image/webp">
					<source srcset="<?= $urlGenerator->assetUrl("images/characters/{$id}.jpg") ?>" type="image/jpeg">
					<img src="<?= $urlGenerator->assetUrl("images/characters/{$id}.jpg") ?>" alt="" />
				</picture>
			</a>
		</article>
		<?php endif ?>
	<?php endforeach ?>
	</section>
	<?php endforeach ?>
	<?php endif ?>

	<?php if (count($staff) > 0): ?>
	<br />
	<hr />
	<h2>Staff</h2>

	<?php foreach($staff as $role => $people): ?>
		<h3><?= $role ?></h3>
		<section class='media-wrap flex flex-wrap flex-justify-start'>
		<?php foreach($people as $pid => $person): ?>
			<article class='character person'>
				<?php $link = $url->generate('person', ['id' => $pid]) ?>
				<div class="name">
					<a href="<?= $link ?>">
						<?= $person['name'] ?>
					</a>
				</div>
				<a href="<?= $link ?>">
					<picture>
						<source
							srcset="<?= $urlGenerator->assetUrl("images/people/{$pid}.webp") ?>"
							type="image/webp"
						>
						<source
							srcset="<?= $urlGenerator->assetUrl("images/people/{$pid}.jpg") ?>"
							type="image/jpeg"
						>
						<img src="<?= $urlGenerator->assetUrl("images/people/{$pid}.jpg") ?>" alt="" />
					</picture>
				</a>
			</article>
		<?php endforeach ?>
		</section>
	<?php endforeach ?>
	<?php endif ?>
</main>