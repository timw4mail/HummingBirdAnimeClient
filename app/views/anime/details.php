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
					<td><?= $data['age_rating'] ?></td>
				</tr>
				<tr>
					<td>Genres</td>
					<td>
						<?php
						$genres = [];
						foreach($data['genres'] as $g) $genres[] = $g['name'];
						?>
						<?= implode(', ', $genres); ?>
					</td>
				</tr>
			</table>
		</div>
		<div>
			<h2><a target="_blank" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php if( ! empty($data['alternate_title'])): ?>
				<h3><?= $data['alternate_title'] ?></h3>
			<?php endif ?>

			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
		</div>
	</section>
</main>