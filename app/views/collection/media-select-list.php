<select name="media_id[]" id="media_id" multiple size="13">
	<?php foreach ($media_items as $group => $items): ?>
		<optgroup label='<?= $group ?>'>
			<?php foreach ($items as $id => $name): ?>
				<option <?= in_array($id, ($item['media_id'] ?? []), FALSE) ? 'selected="selected"' : '' ?> value="<?= $id ?>">
					<?= $name ?>
				</option>
			<?php endforeach ?>
		</optgroup>
	<?php endforeach ?>
</select>