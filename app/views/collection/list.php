<main>
<?php /* if (is_logged_in()): ?>
[<a href="<?= $urlGenerator->full_url('collection/add', 'anime') ?>">Add Item</a>]
<?php endif */ ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<table>
		<thead>
			<tr>
				<th>Title</th>
				<?php /*<th>Alternate Title</th>*/ ?>
				<th>Episode Count</th>
				<th>Episode Length</th>
				<th>Show Type</th>
				<th>Age Rating</th>
				<th>Notes</th>
				<?php /*if (is_logged_in()): ?>
				<th>&nbsp;</th>
				<?php endif*/ ?>
			</tr>
		</thead>
		<tbody>
		<?php foreach($items as $item): ?>
			<tr>
				<td class="align_left">
					<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
						<?= $item['title'] ?>
					</a>
					<?= ( ! empty($item['alternate_title'])) ? " &middot; " . $item['alternate_title'] : "" ?>
				</td>
				<?php /*<td class="align_left">
					<a href="https://hummingbird.me/anime/<?= $item['slug'] ?>">
						<?= $item['title'] ?>
					</a>
				</td>
				<td class="align_left"><?= $item['alternate_title'] ?></td>*/ ?>
				<td><?= $item['episode_count'] ?></td>
				<td><?= $item['episode_length'] ?></td>
				<td><?= $item['show_type'] ?></td>
				<td><?= $item['age_rating'] ?></td>
				<td class="align_left"><?= $item['notes'] ?></td>
				<?php /* if (is_logged_in()): ?>
				<td>[<a href="<?= $urlGenerator->full_url("collection/edit/{$item['hummingbird_id']}", "anime") ?>">Edit</a>]</td>
				<?php endif */ ?>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<br />
	<?php endforeach ?>
<?php endif ?>
</main>
<script src="<?= $urlGenerator->asset_url('js.php?g=table') ?>"></script>