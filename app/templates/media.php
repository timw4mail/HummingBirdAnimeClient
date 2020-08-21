<article class="<?= $className ?>">
	<a href="<?= $link ?>"><?= $picture ?></a>
	<div class="name">
		<a href="<?= $link ?>">
			<?= array_shift($titles) ?>
			<?php foreach ($titles as $title): ?>
				<br />
				<small><?= $title ?></small>
			<?php endforeach ?>
		</a>
	</div>
</article>