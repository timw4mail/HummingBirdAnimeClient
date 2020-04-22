<main class="details fixed">
	<?php if (empty($items)): ?>
		<h3>No recent watch history.</h3>
	<?php else: ?>
		<section>
		<?php foreach ($items as $name => $item): ?>
			<article class="flex flex-no-wrap flex-justify-start">
				<section class="flex-self-center history-img"><?= $helper->picture(
					$item['coverImg'],
					'jpg',
					['width' => '110px', 'height' => '156px'],
					['width' => '110px', 'height' => '156px']
				) ?></section>
				<section class="flex-self-center"><?= $item['action'] ?></section>
			</article>
		<?php endforeach ?>
		</section>
	<?php endif ?>
</main>
