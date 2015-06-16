<main>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<th>Title</th>
				<th>Alternate Title</th>
				<th>Episode Count</th>
				<th>Episode Length</th>
				<th>Show Type</th>
				<th>Age Rating</th>
				<th>Notes</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($items as $item): ?>
			<tr>
				<td class="align_left">
					<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
						<?= $item['title'] ?>
					</a>
				</td>
				<td class="align_left"><?= $item['alternate_title'] ?></td>
				<td><?= $item['episode_count'] ?></td>
				<td><?= $item['episode_length'] ?></td>
				<td><?= $item['show_type'] ?></td>
				<td><?= $item['age_rating'] ?></td>
				<td class="align_left"><?= $item['notes'] ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<br />
	<?php endforeach ?>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="/public/js/table_sorter/jquery.tablesorter.min.js"></script>
<script>
$(function() {
	$('table').tablesorter();
});
</script>