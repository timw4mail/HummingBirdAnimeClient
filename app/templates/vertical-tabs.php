<div class="vertical-tabs">
	<?php $i = 0; ?>
	<?php foreach ($data as $tabName => $tabData): ?>
		<?php $id = "{$name}-{$i}" ?>
		<div class="tab">
			<input
				type="radio"
				role='tab'
				aria-controls="_<?= $id ?>"
				name="staff-roles"
				id="<?= $id ?>"
				<?= $i === 0 ? 'checked="checked"' : '' ?>
			/>
			<label for="<?= $id ?>"><?= $tabName ?></label>
			<section
				id='_<?= $id ?>'
				role="tabpanel"
				class="<?= $className ?>"
			>
				<?= $callback($tabData, $tabName) ?>
			</section>
		</div>
		<?php $i++; ?>
	<?php endforeach ?>
</div>