<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" width="402" height="284" src="<?= $data['cover_image'] ?>" alt="" />
			<br />
			<br />
			<table class="media_details">
				<tr>
					<td class="align_right">Airing Status</td>
					<td><?= $data['status'] ?></td>
				</tr>
				<tr>
					<td>Show Type</td>
					<td><?= $data['show_type'] ?></td>
				</tr>
				<tr>
					<td>Episode Count</td>
					<td><?= $data['episode_count'] ?? '-' ?></td>
				</tr>
				<tr>
					<td>Episode Length</td>
					<td><?= $data['episode_length'] ?> minutes</td>
				</tr>
				<?php if ( ! empty($data['age_rating'])): ?>
				<tr>
					<td>Age Rating</td>
                    <td><abbr title="<?= $data['age_rating_guide'] ?>"><?= $data['age_rating'] ?></abbr></td>
				</tr>
				<?php endif ?>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $data['genres']) ?>
					</td>
				</tr>
			</table>
		</div>
		<div>
			<h2><a rel="external" href="<?= $data['url'] ?>"><?= array_shift($data['titles']) ?></a></h2>
            <?php foreach ($data['titles'] as $title): ?>
                <h3><?= $title ?></h3>
            <?php endforeach ?>
			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
			<?php if (count($data['streaming_links']) > 0): ?>
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
				<?php foreach($data['streaming_links'] as $link): ?>
					<tr>
						<td class="align_left">
							<?php if ($link['meta']['link'] !== FALSE): ?>
							<a href="<?= $link['link'] ?>" title="Stream '<?= $data['title'] ?>' on <?= $link['meta']['name'] ?>">
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
			<?php /*<pre><?= print_r($data, TRUE) ?></pre> */ ?>
		</div>
	</section>
</main>