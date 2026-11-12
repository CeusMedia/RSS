<?php
declare( strict_types=1 );

/**
 *	Shared setup for demo scripts.
 *	Run demos on the command line, e.g. "php demo/create.php",
 *	or start a web server with "php -S localhost:8080 -t demo" and open http://localhost:8080/.
 */

$autoload	= dirname( __DIR__ ).'/vendor/autoload.php';
if( !file_exists( $autoload ) ){
	print 'Please install dependencies first: composer install'.PHP_EOL;
	exit( 1 );
}
require_once $autoload;

date_default_timezone_set( 'UTC' );

if( 'cli' !== PHP_SAPI && !headers_sent() )
	header( 'Content-Type: text/plain; charset=utf-8' );

/**
 *	Prints a headline, followed by an empty line.
 */
function headline( string $text ): void
{
	print PHP_EOL.$text.PHP_EOL.str_repeat( '=', mb_strlen( $text ) ).PHP_EOL.PHP_EOL;
}

function formatDate( ?DateTimeInterface $date ): string
{
	return $date?->format( 'Y-m-d H:i O' ) ?? '-';
}

/**
 *	Prints error message (to STDERR on command line) and exits.
 */
function fail( string $message ): never
{
	if( 'cli' === PHP_SAPI )
		fwrite( STDERR, $message.PHP_EOL );
	else
		print $message.PHP_EOL;
	exit( 1 );
}
