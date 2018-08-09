<main>
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
<?php $i = 0; ?>
	<div class="tabs">
	<?php foreach ($sections as $name => $items): ?>
			<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="collection-tab-<?= $i ?>" name="collection-tabs" />
		<label for="collection-tab-<?= $i ?>"><?= $name ?></label>
	<div class="content">
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<?php if($auth->isAuthenticated()): ?>
				<th>Actions</th>
				<?php endif ?>
				<th>Title</th>
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
				<?php if($auth->isAuthenticated()): ?>
				<td>
					<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.edit.get', ['id' => $item['hummingbird_id']]) ?>">Edit</a>
				</td>
				<?php endif ?>
				<td class="align_left">
					<a href="<?= $url->generate('anime.details', ['id' => $item['slug']]) ?>">
						<?= $item['title'] ?>
					</a>
					<?= ( ! empty($item['alternate_title'])) ? " <br /><small> " . $item['alternate_title'] . "</small>" : "" ?>
				</td>
				<td><?= $item['episode_count'] ?></td>
				<td><?= $item['episode_length'] ?></td>
				<td><?= $item['show_type'] ?></td>
				<td><?= $item['age_rating'] ?></td>
				<td class="align_left"><?= $item['notes'] ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	</div>
	<?php $i++ ?>
	<?php endforeach ?>
	</div>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js.php/g/table') ?>"></script>