<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" src="<?= $data['cover_image'] ?>" alt="<?= $data['title'] ?> cover image" />
			<br />
			<br />
			<table>
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
					<td><?= $data['episode_count'] ?></td>
				</tr>
				<tr>
					<td>Episode Length</td>
					<td><?= $data['episode_length'] ?> minutes</td>
				</tr>
				<tr>
					<td>Age Rating</td>
                    <td><abbr title="<?= $data['age_rating_guide'] ?>"><?= $data['age_rating'] ?></abbr></td>
				</tr>
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
		</div>
	</section>
</main>