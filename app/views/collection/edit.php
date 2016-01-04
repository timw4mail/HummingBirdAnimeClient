<?php if ($auth->is_authenticated()): ?>
<main>
	<h2>Edit Anime Collection Item</h2>
	<form action="<?= $action_url ?>" method="post">
		<table class="form">
			<thead>
				<tr>
					<th>
						<h3><?= $escape->html($item['title']) ?></h3>
						<?php if($item['alternate_title'] != ""): ?>
						<h4><?= $escape->html($item['alternate_title']) ?></h4>
						<?php endif ?>
					</th>
					<th>
						<article class="media">
							<?= $helper->img($item['cover_image']); ?>
						</article>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="media_id">Media</label></td>
					<td>
						<select name="media_id" id="media_id">
						<?php foreach($media_items as $id => $name): ?>
							<option <?= $item['media_id'] == $id ? 'selected="selected"' : '' ?> value="<?= $id ?>"><?= $name ?></option>
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
</main>
<template id="show_list">
	<article class="media">
		<div class="name"><label><input type="radio" name="id" value="{{:id}}" />&nbsp;<span>{{:title}}<br />{{:alternate_title}}</span></label></div>
		<img src="{{:cover_image}}" alt="{{:title}}"  />
	</article>
</template>
<script src="<?= $urlGenerator->asset_url('js.php?g=anime_collection') ?>"></script>
<?php endif ?>