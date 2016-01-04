<main>
<?php if ($auth->is_authenticated()): ?>
<a class="bracketed" href="<?= $urlGenerator->url('anime/add', 'anime') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<?php if($auth->is_authenticated()): ?>
				<th>&nbsp;</th>
				<?php endif ?>
				<th>Title</th>
				<th>Airing Status</th>
				<th>Score</th>
				<th>Type</th>
				<th>Progress</th>
				<th>Rated</th>
				<th>Attributes</th>
				<th>Notes</th>
				<th>Genres</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<?php if ($item['private'] && ! $auth->is_authenticated()) continue; ?>
			<tr id="a-<?= $item['id'] ?>">
				<?php if ($auth->is_authenticated()): ?>
				<td>
					<a class="bracketed" href="<?= $urlGenerator->url("/anime/edit/{$item['id']}/{$item['watching_status']}") ?>">Edit</a>
				</td>
				<?php endif ?>
				<td class="align_left">
					<a href="<?= $item['anime']['url'] ?>" target="_blank">
						<?= $item['anime']['title'] ?>
					</a>
					<?= ( ! empty($item['anime']['alternate_title'])) ? " <br /> " . $item['anime']['alternate_title'] : "" ?>
				</td>
				<td class="align_left"><?= $item['airing']['status'] ?></td>
				<td><?= $item['user_rating'] ?> / 10 </td>
				<td><?= $item['anime']['type'] ?></td>
				<td id="<?= $item['anime']['slug'] ?>">
					Episodes: <br />
					<span class="completed_number"><?= $item['episodes']['watched'] ?></span>&nbsp;/&nbsp;<span class="total_number"><?= $item['episodes']['total'] ?></span>
				</td>
				<td><?= $item['anime']['age_rating'] ?></td>
				<td>
					<?php $attr_list = []; ?>
					<?php foreach(['private','rewatching'] as $attr): ?>
						<?php if($item[$attr]): ?>
						<?php $attr_list[] = ucfirst($attr); ?>
						<?php endif ?>
					<?php endforeach ?>
					<?= implode(', ', $attr_list); ?>
				</td>
				<td>
					<p><?= $escape->html($item['notes']) ?></p>
				</td>
				<td class="align_left">
					<?php sort($item['anime']['genres']) ?>
					<?= join(', ', $item['anime']['genres']) ?>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
<?php endif ?>
</main>
<?php $group = ($auth->is_authenticated()) ? 'table_edit' : 'table' ?>
<script src="<?= $urlGenerator->asset_url("js.php?g={$group}") ?>"></script>
