<main class="details">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" src="<?= $data['cover_image'] ?>" alt="<?= $data['title'] ?> cover image" />
			<br />
			<br />
			<table>
				<tr>
					<td>Manga Type</td>
					<td><?= $data['manga_type'] ?></td>
				</tr>
				<tr>
					<td>Volume Count</td>
					<td><?= $data['volume_count'] ?></td>
				</tr>
				<tr>
					<td>Chapter Count</td>
					<td><?= $data['chapter_count'] ?></td>
				</tr>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $data['genres']); ?>
					</td>
				</tr>
			</table>
		</div>
		<div>
			<h2><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php if( ! empty($data['en_title'])): ?>
				<h3><?= $data['en_title'] ?></h3>
			<?php endif ?>

			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
		</div>
	</section>
</main>