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
				<th>Airing Status</th>
				<th>Score</th>
				<th>Type</th>
				<th>Progress</th>
				<th>Rated</th>
				<th>Notes</th>
				<th>Genres</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr id="a-<?= $item['id'] ?>">
				<td class="align_left">
					<a href="<?= $item['anime']['url'] ?>">
						<?= $item['anime']['title'] ?>
					</a>
					<?= ( ! empty($item['anime']['alternate_title'])) ? " <br /> " . $item['anime']['alternate_title'] : "" ?>
				</td>
				<td class="align_left"><?= $item['airing']['status'] ?></td>
				<td><?= $item['user_rating'] ?> / 10 </td>
				<td><?= $item['anime']['type'] ?></td>
				<td>Episodes: <?= $item['episodes']['watched'] ?> / <?= $item['episodes']['total'] ?></td>
				<td><?= $item['anime']['age_rating'] ?></td>
				<td><?= $item['notes'] ?></td>
				<td class="align-left">
					<ul>
					<?php sort($item['anime']['genres']) ?>
					<?php foreach($item['anime']['genres'] as $genre): ?>
						<li><?= $genre ?></li>
					<?php endforeach ?>
					</ul>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
<?php endif ?>
</main>
<script src="<?= $urlGenerator->asset_url('js.php?g=table') ?>"></script>