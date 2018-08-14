<tr>
	<?php if ($auth->isAuthenticated()): ?>
		<td>
			<a class="bracketed"
			   href="<?= $url->generate($collection_type . '.collection.edit.get', ['id' => $item['hummingbird_id']]) ?>">Edit</a>
		</td>
	<?php endif ?>
	<td class="align_left">
		<a href="<?= $url->generate('anime.details', ['id' => $item['slug']]) ?>">
			<?= $item['title'] ?>
		</a>
		<?= (!empty($item['alternate_title'])) ? " <br /><small> " . $item['alternate_title'] . "</small>" : "" ?>
	</td>
	<td><?= $item['episode_count'] ?></td>
	<td><?= $item['episode_length'] ?></td>
	<td><?= $item['show_type'] ?></td>
	<td><?= $item['age_rating'] ?></td>
	<td class="align_left"><?= $item['notes'] ?></td>
</tr>