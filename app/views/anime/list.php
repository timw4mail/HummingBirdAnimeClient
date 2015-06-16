<main>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<th>Title</th>
				<th>Alternate Title</th>
				<th>Airing Status</th>
				<th>Score</th>
				<th>Type</th>
				<th>Progress</th>
				<th>Rated</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr id="a-<?= $item['anime']['id'] ?>">
				<td class="align_left">
					<a href="<?= $item['anime']['url'] ?>">
						<?= $item['anime']['title'] ?>
					</a>
				</td>
				<td class="align_left"><?= $item['anime']['alternate_title'] ?></td>
				<td class="align_left"><?= $item['anime']['status'] ?></td>
				<td><?= (int)($item['rating']['value'] * 2) ?> / 10 </td>
				<td><?= $item['anime']['show_type'] ?></td>
				<td>Episodes: <?= $item['episodes_watched'] ?> / <?= $item['anime']['episode_count'] ?></td>
				<td><?= $item['anime']['age_rating'] ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
</main>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="/public/js/table_sorter/jquery.tablesorter.min.js"></script>
<script>
$(function() {
	$('table').tablesorter();
});
</script>