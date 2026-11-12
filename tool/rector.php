<?php

declare( strict_types=1 );

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

/**
 *	Rector configuration.
 *
 *	Dry run:	composer dev:rector
 *	Apply:		composer dev:rector-fix
 *
 *	Kept conservative on purpose: only PHP-level upgrades up to the minimum
 *	PHP version declared in composer.json, no code-quality or coding-style sets.
 */
return RectorConfig::configure()
	->withPaths( [
		__DIR__.'/../src',
		__DIR__.'/../test',
		__DIR__.'/../demo',
	] )
	->withPhpSets( php83: TRUE )
	->withSkip( [
		//  keep long array syntax where it is still used
		LongArrayToShortArrayRector::class,
		//  keep all properties of models in one documented list
		ClassPropertyAssignToConstructorPromotionRector::class	=> [__DIR__.'/../src/Model'],
	] );
