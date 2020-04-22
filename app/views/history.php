<main class="details fixed">
	<?php if (empty($items)): ?>
		<h3>No recent history.</h3>
	<?php else: ?>
		<section>
		<?php foreach ($items as $name => $item): ?>
			<article class="flex flex-no-wrap flex-justify-start">
				<section class="flex-self-center history-img">
					<a href="<?= $item['url'] ?>">
					<?= $helper->picture(
						$item['coverImg'],
						'jpg',
						['width' => '110px', 'height' => '156px'],
						['width' => '110px', 'height' => '156px']
					) ?>
					</a>
				</section>
				<section class="flex-self-center">
					<?= $helper->a($item['url'], $item['title']) ?>
					<br />
					<br />
					<?= $item['action'] ?>
					<br />
					<small>
						<?php if ( ! empty($item['dateRange'])):
							[$startDate, $endDate] = array_map(
								fn ($date) => $date->format('l, F d'),
								$item['dateRange']
							);
							[$startTime, $endTime] = array_map(
									fn ($date) => $date->format('h:i:s A'),
									$item['dateRange']
							);
							?>
							<?php if ($startDate === $endDate): ?>
								<?= "{$startDate}, {$startTime} &ndash; {$endTime}" ?>
							<?php else: ?>
								<?= "{$startDate} {$startTime} &ndash; {$endDate} {$endTime}" ?>
							<?php endif ?>
						<?php else: ?>
							<?= $item['updated']->format('l, F d h:i:s A') ?>
						<?php endif ?>
					</small>
				</section>
			</article>
		<?php endforeach ?>
		</section>
	<?php endif ?>
</main>
