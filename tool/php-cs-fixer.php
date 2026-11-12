<?php
declare( strict_types=1 );

/**
 *	PHP-CS-Fixer configuration matching the coding style of CeusMedia projects:
 *	tabs, uppercase constants, spaces inside parentheses, ordered imports.
 *
 *	Check:	composer dev:cs
 *	Fix:	composer dev:cs-fix
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder	= Finder::create()
	->in( [__DIR__.'/../src', __DIR__.'/../test', __DIR__.'/../demo', __DIR__] )
	->name( '*.php' );

return ( new Config() )
	->setFinder( $finder )
	->setParallelConfig( ParallelConfigFactory::detect() )
	->setCacheFile( __DIR__.'/../.php-cs-fixer.cache' )
	->setIndent( "\t" )
	->setLineEnding( "\n" )
	->setRiskyAllowed( TRUE )
	->setRules( [
		'encoding'						=> TRUE,
		'full_opening_tag'				=> TRUE,
		'no_closing_tag'				=> TRUE,
		'indentation_type'				=> TRUE,
		'line_ending'					=> TRUE,
		'no_trailing_whitespace'		=> TRUE,
		'no_whitespace_in_blank_line'	=> TRUE,
		'single_blank_line_at_eof'		=> TRUE,
		'blank_line_after_namespace'	=> TRUE,
		'blank_line_after_opening_tag'	=> FALSE,
		'constant_case'					=> ['case' => 'upper'],
		'lowercase_keywords'			=> TRUE,
		'lowercase_static_reference'	=> TRUE,
		'magic_constant_casing'			=> TRUE,
		'native_function_casing'		=> TRUE,
		'no_unused_imports'				=> TRUE,
		'ordered_imports'				=> ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
		'single_import_per_statement'	=> TRUE,
		'declare_strict_types'			=> TRUE,
		'strict_comparison'				=> TRUE,
		'spaces_inside_parentheses'		=> ['space' => 'single'],
	] );
