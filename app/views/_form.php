<?php
// Higher scoped variables:
// $fields
// $hiddenFields
// $nestedPrefix
if ( ! function_exists('subfieldRender'))
{
	function subfieldRender ($nestedPrefix, $fields, &$hiddenFields, $helper, $section)
	{
		include '_form.php';
	}
}

?>

<?php foreach ($fields as $name => $field): ?>
	<?php $fieldname = ($section === 'config' || $nestedPrefix !== 'config') ? "{$nestedPrefix}[{$name}]" : "{$nestedPrefix}[{$section}][{$name}]"; ?>
	<?php if ($field['type'] === 'subfield'): ?>
		<section>
			<h4><?= $field['title'] ?></h4>
			<?php subfieldRender($fieldname, $field['fields'], $hiddenFields, $helper, $section); ?>
		</section>
	<?php elseif ( ! empty($field['display'])): ?>
		<article>
			<label for="<?= $fieldname ?>"><?= $field['title'] ?></label><br />
			<small><?= $field['description'] ?></small><br />
			<?= $helper->field($fieldname, $field); ?>
		</article>
	<?php else: ?>
		<?php $hiddenFields[] = $helper->field($fieldname, $field); ?>
	<?php endif ?>
<?php endforeach ?>
