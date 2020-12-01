<div class="tabs">
	<?php $i = 0; foreach ($data as $tabName => $tabData): ?>
		<?php if ( ! empty($tabData)): ?>
			<?php $id = "{$name}-{$i}"; ?>
			<input
				role='tab'
				aria-controls="_<?= $id ?>"
				type="radio"
				name="<?= $name ?>"
				id="<?= $id ?>"
				<?= ($i === 0) ? 'checked="checked"' : '' ?>
			/>
			<label for="<?= $id ?>"><?= ucfirst($tabName) ?></label>

			<?php if ($hasSectionWrapper): ?>
			<div class="content full-height">
			<?php endif ?>

			<section
				id="_<?= $id ?>"
				role="tabpanel"
				class="<?= $className ?>"
			>
				<?= $callback($tabData, $tabName) ?>
			</section>

			<?php if ($hasSectionWrapper): ?>
			</div>
			<?php endif ?>
		<?php endif ?>
	<?php $i++; endforeach ?>
</div>