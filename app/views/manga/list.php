<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate('manga.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<br />
	<label>Filter: <input type='text' class='media-filter' /></label>
	<br />
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<?php if (empty($items)): ?>
		<h3>There's nothing here!</h3>
	<?php else: ?>
		<table class='media-wrap'>
			<thead>
				<tr>
					<?php if ($auth->isAuthenticated()): ?>
					<td>&nbsp;</td>
					<?php endif ?>
					<th>Title</th>
					<th>Rating</th>
					<th>Completed Chapters</th>
					<th># of Volumes</th>
					<th>Attributes</th>
					<th>Type</th>
					<th>Genres</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($items as $item): ?>
				<tr id="manga-<?= $item['id'] ?>">
					<?php if($auth->isAuthenticated()): ?>
					<td>
						<a class="bracketed" href="<?= $url->generate('edit', [
							'controller' => 'manga',
							'id' => $item['id'],
							'status' => $name
						]) ?>">Edit</a>
					</td>
					<?php endif ?>
					<td class="align-left">
						<a href="<?= $url->generate('manga.details', ['id' => $item['manga']['slug']]) ?>">
							<?= $item['manga']['title'] ?>
						</a>
						<?php foreach($item['manga']['titles'] as $title): ?>
	                        <br /><?= $title ?>
						<?php endforeach ?>
					</td>
					<td><?= $item['user_rating'] ?> / 10</td>
					<td><?= $item['chapters']['read'] ?> / <?= $item['chapters']['total'] ?></td>
					<td><?= $item['volumes']['total'] ?></td>
					<td>
	                    <ul>
						<?php if ($item['reread'] > 0): ?>
	                        <li>Reread <?= $item['reread'] ?> time(s)</li>
						<?php endif ?>
						<?php foreach(['rereading'] as $attr): ?>
							<?php if($item[$attr]): ?>
	                            <li><?= ucfirst($attr); ?></li>
							<?php endif ?>
						<?php endforeach ?>
	                    </ul>
					</td>
					<td><?= $item['manga']['type'] ?></td>
					<td class="align-left">
						<?= implode(', ', $item['manga']['genres']) ?>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php endif ?>
	<?php endforeach ?>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>
