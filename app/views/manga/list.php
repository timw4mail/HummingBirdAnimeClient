<main>
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $urlGenerator->url('manga/add') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<?php if ($auth->isAuthenticated()): ?>
				<th>&nbsp;</th>
				<?php endif ?>
				<th>Title</th>
				<th>Rating</th>
				<th>Completed Chapters</th>
				<th># of Volumes</th>
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $item): ?>
			<tr id="manga-<?= $item['id'] ?>">
				<?php if($auth->isAuthenticated()): ?>
				<td>
					<a class="bracketed" href="<?= $urlGenerator->url("manga/edit/{$item['id']}/{$name}") ?>">Edit</a>
				</td>
				<?php endif ?>
				<td class="align_left">
					<a href="<?= $url->generate('manga.details', ['id' => $item['manga']['slug']]) ?>">
						<?= array_shift($item['manga']['titles']) ?>
					</a>
					<?php foreach($item['manga']['titles'] as $title): ?>
                        <br /><?= $title ?>
					<?php endforeach ?>
				</td>
				<td><?= $item['user_rating'] ?> / 10</td>
				<td><?= $item['chapters']['read'] ?> / <?= $item['chapters']['total'] ?></td>
				<td><?= $item['volumes']['total'] ?></td>
				<td><?= $item['manga']['type'] ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js.php/g/table') ?>"></script>