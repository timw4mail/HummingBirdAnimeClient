<?php if ($auth->isAuthenticated()): ?>
<main>
	<h2>Edit Anime Collection Item</h2>
	<form action="<?= $action_url ?>" method="post">
		<table class="invisible form">
			<tbody>
				<tr>
					<td rowspan="6" class="align-center">
						<article class="media">
						<?= $helper->img($urlGenerator->assetUrl("images/anime/{$item['hummingbird_id']}.jpg")); ?>
						</article>
					</td>
				</tr>
				<tr>
					<td class="align-right"><label for="title">Title</label></td>
					<td class="align-left">
						<input type="text" id="title" name="title" value="<?= $item['title'] ?>" />
					</td>
				</tr>
				<tr>
					<td class="align-right"><label for="alternate_title">Alternate Title</label></td>
					<td class="align-left">
						<input type="text" id="alternate_title" name="alternate_title" value="<?= $item['alternate_title'] ?>"/>
					</td>
				</tr>
				<tr>
					<td class="align-right"><label for="media_id">Media</label></td>
					<td class="align-left">
						<select name="media_id" id="media_id">
						<?php foreach($media_items as $id => $name): ?>
							<option <?= $item['media_id'] === (string)$id ? 'selected="selected"' : '' ?> value="<?= $id ?>"><?= $name ?></option>
						<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="notes">Notes</label></td>
					<td><textarea id="notes" name="notes"><?= $escape->html($item['notes']) ?></textarea></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<?php if($action === 'Edit'): ?>
						<input type="hidden" name="hummingbird_id" value="<?= $item['hummingbird_id'] ?>" />
						<?php endif ?>
						<button type="submit">Save</button>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<fieldset>
		<legend>Danger Zone</legend>
		<form class="js-delete" action="<?= $url->generate($collection_type . '.collection.delete') ?>" method="post">
			<table class="form invisible">
				<tbody>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="hidden" value="<?= $item['hummingbird_id'] ?>" name="hummingbird_id" />
						<button type="submit" class="danger">Delete Entry</button>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</fieldset>
</main>
<?php endif ?>