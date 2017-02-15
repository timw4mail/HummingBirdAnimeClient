<main>
<?php if ($auth->is_authenticated()): ?>
<a class="bracketed" href="<?= $urlGenerator->fullUrl('collection/add', 'anime') ?>">Add Item</a>
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
				<?php if($auth->is_authenticated()): ?>
				<td>
					<a class="bracketed" href="<?= $urlGenerator->fullUrl("collection/edit/{$item['hummingbird_id']}") ?>">Edit</a>
					<?php /*<a class="bracketed" href="<?= $urlGenerator->fullUrl("collection/delete/{$item['hummingbird_id']}") ?>">Delete</a>*/ ?>
				</td>
				<?php endif ?>
				<td class="align_left">
					<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
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
	<br />
	<?php endforeach ?>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js.php/g/table') ?>"></script>