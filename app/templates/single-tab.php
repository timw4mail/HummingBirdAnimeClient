<section class="<?= $className ?>">
	<?php foreach ($data as $tabName => $tabData): ?>
	<?= $callback($tabData, $tabName) ?>
	<?php endforeach ?>
</section>