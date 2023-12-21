<tr>
	<?php if ($_->isAuthenticated()): ?>
		<td>
			<a class="bracketed"
			   href="<?= $_->urlFromRoute($collection_type . '.collection.edit.get', ['id' => $item['hummingbird_id']]) ?>">Edit</a>
		</td>
	<?php endif ?>
	<td class="align-left">
		<a href="<?= $_->urlFromRoute('anime.details', ['id' => $item['slug']]) ?>">
			<?= $item['title'] ?>
		</a>
		<?= ! empty($item['alternate_title']) ? ' <br /><small> ' . $item['alternate_title'] . '</small>' : '' ?>
	</td>
	<?php if ($hasMedia): ?>
	<td><?= implode(', ', $item['media']) ?></td>
	<?php endif ?>
	<td><?= ($item['episode_count'] > 1) ? $item['episode_count'] : '-'  ?></td>
	<td><?= $item['episode_length'] ?></td>
	<td><?= $item['show_type'] ?></td>
	<td><?= $item['age_rating'] ?></td>
	<?php if ($hasNotes): ?><td class="align-left"><?= nl2br($item['notes'] ?? '', TRUE) ?></td><?php endif ?>
	<td class="align-left"><?= implode(', ', $item['genres']) ?></td>
</tr>