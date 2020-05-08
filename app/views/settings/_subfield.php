<?php
// Higher scoped variables:
// $field
// $fields
// $hiddenFields
// $nestedPrefix
?>

<?php foreach ($field['fields'] as $name => $field): ?>
	<?php
		$fieldName = ($section === 'config' || $nestedPrefix !== 'config')
			? "{$nestedPrefix}[{$name}]"
			: "{$nestedPrefix}[{$section}][{$name}]";
	?>
	<?php if ( ! empty($field['display'])): ?>
		<?php include '_field.php' ?>
	<?php else: ?>
		<?php $hiddenFields[] = $helper->field($fieldName, $field); ?>
	<?php endif ?>
<?php endforeach ?>