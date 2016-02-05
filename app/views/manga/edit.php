<?php if ($auth->is_authenticated()): ?>
	<main>
		<h1>
			Edit <?= $item['manga']['title'] ?>
			<?= ($item['manga']['alternate_title'] != "") ? "({$item['manga']['alternate_title']})" : ""; ?>
		</h1>
		<form action="<?= $action ?>" method="post">
		<table class="form">
			<thead>
				<tr>
					<th>
						<h3><?= $escape->html($item['manga']['title']) ?></h3>
						<?php if($item['manga']['alternate_title'] != ""): ?>
						<h4><?= $escape->html($item['manga']['alternate_title']) ?></h4>
						<?php endif ?>
					</th>
					<th>
						<article class="media">
							<?= $helper->img($item['manga']['image']); ?>
						</article>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><label for="status">Reading Status</label></td>
					<td>
						<select name="status" id="status">
						<?php foreach($status_list as $status): ?>
							<option <?php if($item['reading_status'] === $status): ?>selected="selected"<?php endif ?>
								value="<?= $status ?>"><?= $status ?></option>
						<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="series_rating">Rating</label></td>
					<td>
						<input type="number" min="0" max="10" maxlength="2" name="new_rating" value="<?= $item['user_rating'] ?>" id="series_rating" size="2" /> / 10
					</td>
				</tr>
				<tr>
					<td><label for="chapters_read">Chapters Read</label></td>
					<td>
						<input type="number" min="0" name="chapters_read" id="chapters_read" value="<?= $item['chapters']['read'] ?>" /> / <?= $item['chapters']['total'] ?>
					</td>
				</tr>
				<tr>
					<td><label for="volumes_read">Volumes Read</label></td>
					<td>
						<input type="number" min="0" name="volumes_read" id="volumes_read" value="<?= $item['volumes']['read'] ?>" /> / <?= $item['volumes']['total'] ?>
					</td>
				</tr>
				<tr>
					<td><label for="rereading_flag">Rereading?</label></td>
					<td>
						<input type="checkbox" name="reareading" id="rereading_flag"
							<?php if($item['rereading'] === TRUE): ?>checked="checked"<?php endif ?>
						/>
					</td>
				</tr>
				<tr>
					<td><label for="reread_count">Reread Count</label></td>
					<td>
						<input type="number" min="0" id="reread_count" name="reread_count" value="<?= $item['reread'] ?>" />
					</td>
				</tr>
				<tr>
					<td><label for="notes">Notes</label></td>
					<td>
						<textarea name="notes" id="notes"><?= $escape->html($item['notes']) ?></textarea>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="hidden" value="<?= $item['id'] ?>" name="id" />
						<input type="hidden" value="<?= $item['manga']['slug'] ?>" name="manga_id" />
						<input type="hidden" value="<?= $item['user_rating'] ?>" name="old_rating" />
						<input type="hidden" value="true" name="edit" />
						<button type="submit">Submit</button>
					</td>
				</tr>
			</tbody>
		</table>
		</form>
	</main>
<?php endif ?>