<?php if ($auth->is_authenticated()): ?>
<main>
	<h2>Add Anime to your Collection</h2>
	<form action="<?= $action_url ?>" method="post">
		<section>
			<label for="search">Search for anime by name:&nbsp;&nbsp;&nbsp;&nbsp;<input type="search" id="search" name="search" /></label>
			<section id="series_list" class="media-wrap">
			</section>
		</section>
		<br />
		<table class="form">
			<tbody>
				<tr>
					<td><label for="media_id">Media</label></td>
					<td>
						<select name="media_id" id="media_id">
						<?php foreach($media_items as $id => $name): ?>
							<option value="<?= $id ?>"><?= $name ?></option>
						<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="notes">Notes</label></td>
					<td><textarea id="notes" name="notes"></textarea></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
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
		<img src="{{:cover_image}}" alt="{{:title}}" />
	</article>
</template>
<script src="<?= $urlGenerator->asset_url('js.php/g/anime_collection') ?>"></script>
<?php endif ?>