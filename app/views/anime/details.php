<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" width="402" height="284" src="<?= $urlGenerator->assetUrl("images/anime/{$show_data['id']}.jpg") ?>" alt="" />
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
				<tr>
					<td>Episode Length</td>
					<td><?= $show_data['episode_length'] ?> minutes</td>
				</tr>
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
		</div>
		<div>
			<h2><a rel="external" href="<?= $show_data['url'] ?>"><?= array_shift($show_data['titles']) ?></a></h2>
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
		</div>
	</section>

	<?php if (count($characters) > 0): ?>
	<h2>Characters</h2>
	<section class="align_left media-wrap">
	<?php foreach($characters as $id => $char): ?>
		<?php if ( ! empty($char['image']['original'])): ?>
		<article class="character">
			<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
			<div class="name">
				<?= $helper->a($link, $char['name']); ?>
			</div>
			<a href="<?= $link ?>">
			<?= $helper->img($urlGenerator->assetUrl("images/characters/{$id}.jpg"), [
				'width' => '225'
			]) ?>
			</a>
		</article>
		<?php endif ?>
	<?php endforeach ?>
	</section>
	<?php endif ?>
</main>