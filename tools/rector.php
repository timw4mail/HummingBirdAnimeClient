<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

if ( ! function_exists('walk_array'))
{
	function walk_array(callable $method, array $items): void
	{
		foreach ($items as $item) {
			$method($item);
		}
	}
}

return static function (ContainerConfigurator $config): void {
	$parameters = $config->parameters();
	$parameters->set(Option::AUTO_IMPORT_NAMES, false);
	$parameters->set(Option::IMPORT_SHORT_CLASSES, false);
	$parameters->set(Option::SKIP, [
		ReadOnlyPropertyRector::class,
		RestoreDefaultNullToNullableTypePropertyRector::class,
	]);

	walk_array([$config, 'import'], [
		LevelSetList::UP_TO_PHP_80,
	]);

	$services = $config->services();
	walk_array([$services, 'set'], [
		AddArrayDefaultToArrayPropertyRector::class,
		AddArrayParamDocTypeRector::class,
		AddArrayReturnDocTypeRector::class,
		AddClosureReturnTypeRector::class,
		AddMethodCallBasedStrictParamTypeRector::class,
		CallUserFuncArrayToVariadicRector::class,
		CallUserFuncToMethodCallRector::class,
		ChangeIfElseValueAssignToEarlyReturnRector::class,
		ChangeNestedForeachIfsToEarlyContinueRector::class,
		CompleteDynamicPropertiesRector::class,
		ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
		CountArrayToEmptyArrayComparisonRector::class,
		ForRepeatedCountToOwnVariableRector::class,
		ForToForeachRector::class,
		// MakeTypedPropertyNullableIfCheckedRector::class,
		// NewlineAfterStatementRector::class,
		NewlineBeforeNewAssignSetRector::class,
		ParamTypeByMethodCallTypeRector::class,
		ParamTypeByParentCallTypeRector::class,
		RemoveAlwaysElseRector::class,
		RemoveDuplicatedCaseInSwitchRector::class,
		RemoveFinalFromConstRector::class,
		RemoveUnusedForeachKeyRector::class,
		RemoveUselessParamTagRector::class,
		RemoveUselessReturnTagRector::class,
		RemoveUselessVarTagRector::class,
		// SimplifyDeMorganBinaryRector::class,
		SimplifyDuplicatedTernaryRector::class,
		SimplifyIfElseToTernaryRector::class,
		SimplifyIfReturnBoolRector::class,
		SimplifyTautologyTernaryRector::class,
		SwitchNegatedTernaryRector::class,
		TypedPropertyFromAssignsRector::class,
		WrapEncapsedVariableInCurlyBracesRector::class,
	]);
};
