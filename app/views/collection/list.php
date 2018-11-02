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
				<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="collection-tab-<?= $i ?>"
																  name="collection-tabs"/>
				<label for="collection-tab-<?= $i ?>"><h2><?= $name ?></h2></label>
				<div class="content full-height">
					<table class="full_width">
						<thead>
						<tr>
							<?php if ($auth->isAuthenticated()): ?>
								<td>Actions</td>
							<?php endif ?>
							<th>Title</th>
							<th>Episode Count</th>
							<th>Episode Length</th>
							<th>Show Type</th>
							<th>Age Rating</th>
							<th>Genres</th>
							<th>Notes</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($items as $item): ?>
							<?php include __DIR__ . '/list-item.php' ?>
						<?php endforeach ?>
						</tbody>
					</table>
				</div>
				<?php $i++ ?>
			<?php endforeach ?>
		</div>
	<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>