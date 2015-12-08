<?php if ($auth->is_authenticated()): ?>
<main>
	<form action="<?= $action_url ?>" method="post">
	<dl>
		<dt>Series</dt>
		<dd><label>Search for anime name: <input type="search" id="search" /></label></dd>
		<dd>
			<section id="series_list" class="media-wrap">
			</section>
		</dd>

		<dt><label for="media_id">Media</label></dt>
		<dd>
			<select name="media_id" id="media_id">
			<?php foreach($media_items as $id => $name): ?>
				<option value="<?= $id ?>"><?= $name ?></option>
			<?php endforeach ?>
			</select>
		</dd>

		<dt><label for="notes">Notes</label></dt>
		<dd><textarea id="notes" name="notes"></textarea></dd>

		<dt>&nbsp;</dt>
		<dd>
			<button type="submit">Save</button>
		</dd>
	</dl>
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