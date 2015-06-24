<main>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<th>Title</th>
				<th>Alternate Title</th>
				<th>Rating</th>
				<th>Chapters</th>
				<!-- <th>Volumes</th> -->
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr id="manga-<?= $item['manga']['id'] ?>">
				<td class="align_left">
					<a href="https://hummingbird.me/manga/<?= $item['manga']['id'] ?>">
					<?= $item['manga']['romaji_title'] ?>
					</a>
				</td>
				<td class="align_left"><?= (array_key_exists('english_title', $item['manga'])) ? $item['manga']['english_title'] : "" ?></td>
				<td><?= ($item['rating'] > 0) ? (int)($item['rating'] * 2) : '-' ?> / 10</td>
				<td><?= $item['chapters_read'] ?> / <?= ($item['manga']['chapter_count'] > 0) ? $item['manga']['chapter_count'] : "-" ?></td>
				<!-- <td><?= $item['volumes_read'] ?> / <?= ($item['manga']['volume_count'] > 0) ? $item['manga']['volume_count'] : "-" ?></td> -->
				<td><?= $item['manga']['manga_type'] ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
</main>
<script src="<?= asset_url('js.php?g=table') ?>"></script>