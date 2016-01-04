<main>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<?php if ($auth->is_authenticated()): ?>
				<th>&nbsp;</th>
				<?php endif ?>
				<th>Title</th>
				<th>Rating</th>
				<th>Chapters</th>
				<th>Volumes</th>
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr id="manga-<?= $item['id'] ?>">
				<?php if($auth->is_authenticated()): ?>
				<td>
					<a class="bracketed" href="<?= $urlGenerator->url("manga/edit/{$item['id']}/{$name}") ?>">Edit</a>
				</td>
				<?php endif ?>
				<td class="align_left">
					<a href="<?= $item['manga']['url'] ?>">
						<?= $item['manga']['title'] ?>
					</a>
					<?= ( ! is_null($item['manga']['alternate_title'])) ? " &middot; " . $item['manga']['alternate_title'] : "" ?>
				</td>
				<td><?= $item['user_rating'] ?> / 10</td>
				<td><?= $item['chapters']['read'] ?> / <?= $item['chapters']['total'] ?></td>
				<td><?= $item['volumes']['read'] ?> / <?= $item['volumes']['total'] ?></td>
				<td><?= $item['manga']['type'] ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
<?php endif ?>
</main>
<script src="<?= $urlGenerator->asset_url('js.php?g=table') ?>"></script>