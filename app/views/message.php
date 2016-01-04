<div class="message <?= $escape->attr($message_type) ?>">
	<span class="icon"></span>
	<?= $escape->html($message) ?>
	<span class="close" onclick="this.parentElement.style.display='none'">x</span>
</div>