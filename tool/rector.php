<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
use Rector\Set\ValueObject\LevelSetList;

//require_once __DIR__ . '/RectorRule.php';

return static function (RectorConfig $rectorConfig): void {
	$pathSource	= __DIR__.'/../src/';
	$rectorConfig->paths([
		$pathSource,
	]);
	$skipFolders	= [
		/*		$pathSource . '/App',
				$pathSource . '/Client',
				$pathSource . '/Dev',
				$pathSource . '/Info',
				$pathSource . '/Labs',
				$pathSource . '/Manage',
				$pathSource . '/Resource',
				$pathSource . '/Search',
				$pathSource . '/Server',
				$pathSource . '/Test',
				$pathSource . '/UI',
		*/	];
	$skipFiles	= [
		$pathSource . '/RectorRule.php',
	];

	$rectorConfig->rules([
//		Util\RectorRule::class
	]);
	$rectorConfig->sets([
//		LevelSetList::UP_TO_PHP_54,
//		LevelSetList::UP_TO_PHP_55,
//		LevelSetList::UP_TO_PHP_56,
//		LevelSetList::UP_TO_PHP_70,
//		LevelSetList::UP_TO_PHP_71,
//		LevelSetList::UP_TO_PHP_72,
//		LevelSetList::UP_TO_PHP_73,
		LevelSetList::UP_TO_PHP_74,
//		LevelSetList::UP_TO_PHP_80,
//		LevelSetList::UP_TO_PHP_81,
//		LevelSetList::UP_TO_PHP_82,
	]);

	$skipRules	= [
		// Set 5.5
		StringClassNameToClassConstantRector::class,
		// Set 5.6
		PowToExpRector::class,
		//	# inspired by level in psalm - https://github.com/vimeo/psalm/blob/82e0bcafac723fdf5007a31a7ae74af1736c9f6f/tests/FileManipulationTest.php#L1063
//		AddDefaultValueForUndefinedVariableRector::class,
		// Set 7.1
		CountOnNullRector::class,
		// Set 7.3
		JsonThrowOnErrorRector::class,
		IsCountableRector::class,
		RegexDashEscapeRector::class,
		// Set 7.4
		LongArrayToShortArrayRector::class
	];
	$rectorConfig->skip(array_merge($skipFolders, $skipFiles, $skipRules));


//	applied, lately
//	PowToExp



//	NEXT AIM
//  - Set 7.4
//	$rectorConfig->rule(TypedPropertyRector::class);
//	$rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
//		#the_real_type
//		# https://wiki.php.net/rfc/deprecations_php_7_4
//		'is_real' => 'is_float',
//		#apache_request_headers_function
//		# https://wiki.php.net/rfc/deprecations_php_7_4
//		'apache_request_headers' => 'getallheaders',
//	]);
//	$rectorConfig->rule(ArrayKeyExistsOnPropertyRector::class);
//	$rectorConfig->rule(FilterVarToAddSlashesRector::class);
//	$rectorConfig->rule(ExportToReflectionFunctionRector::class);
//	$rectorConfig->rule(MbStrrposEncodingArgumentPositionRector::class);
//	$rectorConfig->rule(RealToFloatTypeCastRector::class);
//	$rectorConfig->rule(NullCoalescingOperatorRector::class);
//	$rectorConfig->rule(ClosureToArrowFunctionRector::class);
//	$rectorConfig->rule(ArraySpreadInsteadOfArrayMergeRector::class);
//	$rectorConfig->rule(AddLiteralSeparatorToNumberRector::class);
//	$rectorConfig->rule(ChangeReflectionTypeToStringToGetNameRector::class);
//	$rectorConfig->rule(RestoreDefaultNullToNullableTypePropertyRector::class);
//	$rectorConfig->rule(CurlyToSquareBracketArrayStringRector::class);

};
