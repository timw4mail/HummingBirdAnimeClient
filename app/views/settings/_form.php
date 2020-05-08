<?php
// Higher scoped variables:
// $fields
// $hiddenFields
// $nestedPrefix
?>

<?php foreach ($fields as $name => $field): ?>
	<?php
		$fieldName = ($section === 'config' || $nestedPrefix !== 'config')
				? "{$nestedPrefix}[{$name}]"
				: "{$nestedPrefix}[{$section}][{$name}]";
	?>
	<?php if ($field['type'] === 'subfield'): ?>
		<section>
			<h4><?= $field['title'] ?></h4>
			<?php include '_subfield.php'; ?>
		</section>
	<?php elseif ( ! empty($field['display'])): ?>
		<?php include '_field.php' ?>
	<?php else: ?>
		<?php $hiddenFields[] = $helper->field($fieldName, $field); ?>
	<?php endif ?>
<?php endforeach ?>
