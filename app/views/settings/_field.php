<article>
	<label for="<?= $fieldName ?>"><?= $field['title'] ?></label><br />
	<small><?= $field['description'] ?></small><br />
	<?= $_->h->field($fieldName, $field); ?>
</article>