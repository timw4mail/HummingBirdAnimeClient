<main>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
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
				<th>Genres</th>
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
				<td class="flex flex-justify-space-around align-left">
					<?php sort($item['anime']['genres']) ?>
					<?php foreach($item['anime']['genres'] as $genre): ?>
						<span><?= $genre['name'] ?></span>
					<?php endforeach ?>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
<?php endif ?>
</main>
<script src="<?= $config->asset_url('js.php?g=table') ?>"></script>