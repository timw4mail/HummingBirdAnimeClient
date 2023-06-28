<?php declare(strict_types=1);

require_once './vendor/autoload.php';

use Aviat\Ion\Attribute;

$namespace = 'Aviat\\AnimeClient\\Controller\\';
$basePath = __DIR__ . '/src/AnimeClient/Controller';
$controllers = glob($basePath . '/*.php');
$classes = array_map(static fn (string $item) => $namespace . basename($item, '.php'), $controllers);

$output = [];

foreach ($classes as $class)
{
	$r = new ReflectionClass($class);
	$rawAttrs = $r->getAttributes();
	$cAttrs = [];

	foreach ($rawAttrs as $attr)
	{
		$cAttrs[$attr->getName()][] = $attr->newInstance();
	}

	$methods = [];

	foreach ($r->getMethods() as $method)
	{
		$attributes = [];

		foreach ($method->getAttributes(Attribute\Route::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute)
		{
			$attributes[$attribute->getName()][] = $attribute->newInstance();
		}

		if ( ! empty($attributes))
		{
			$methods[$method->getName()] = $attributes;
		}
	}

	$key = $r->getName();
	$output[$key] = [];

	if ( ! (empty($cAttrs) && empty($methods)))
	{
		if ( ! empty($cAttrs))
		{
			$output[$key]['attributes'] = $cAttrs;
		}

		if ( ! empty($methods))
		{
			$output[$key]['methods'] = $methods;
		}
	}
}

print_r($output);
