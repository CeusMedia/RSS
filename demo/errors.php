<?php
declare( strict_types=1 );

/**
 *	Demo: Error handling.
 *
 *	- Parser throws ParseException, Renderer throws RenderException, both extend RuntimeException.
 *	- Models and value objects throw InvalidArgumentException for invalid values.
 *	- Parsing is lenient: invalid optional values in a feed are skipped instead of failing.
 *
 *	Usage: php demo/errors.php
 */

use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Exception\RenderException;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Renderer;

require_once __DIR__.'/bootstrap.php';

headline( 'Parsing invalid input' );
$inputs	= [
	'empty string'		=> '',
	'broken XML'		=> '<rss version="2.0"><channel><title>Oops</channel></rss>',
	'Atom feed'			=> '<feed xmlns="http://www.w3.org/2005/Atom"><title>Atom</title></feed>',
	'RSS w/o channel'	=> '<rss version="2.0"/>',
];
foreach( $inputs as $label => $xml ){
	try{
		Parser::parse( $xml );
		printf( "%-18s  parsed\n", $label );
	}
	catch( ParseException $e ){
		printf( "%-18s  %s\n", $label, $e->getMessage() );
	}
}

headline( 'Lenient parsing of invalid optional values' );
$channel	= Parser::parse( '<rss version="2.0"><channel><title>T</title><link>l</link><description>d</description>
	<ttl>soon</ttl><image><url>logo.png</url><width>500</width></image>
	<item><title>x</title><pubDate>yesterday-ish</pubDate></item></channel></rss>' );
printf( "%-18s  %s\n", 'TTL "soon"', var_export( $channel->getTtl(), TRUE ) );
printf( "%-18s  %s\n", 'Image width 500', var_export( $channel->getImage()?->getWidth(), TRUE ) );
printf( "%-18s  %s\n", 'Invalid pubDate', var_export( $channel->getItems()[0]->getPubDate(), TRUE ) );

headline( 'Invalid values in models' );
$factories	= [
	'Image width 200'	=> static fn() => ( new Image( 'logo.png' ) )->setWidth( 200 ),
	'Negative length'	=> static fn() => new Enclosure( 'episode.mp3', -1 ),
	'Skip hour 24'		=> static fn() => ( new Channel() )->setSkipHours( [24] ),
];
foreach( $factories as $label => $factory ){
	try{
		$factory();
		printf( "%-18s  accepted\n", $label );
	}
	catch( InvalidArgumentException $e ){
		printf( "%-18s  %s\n", $label, $e->getMessage() );
	}
}

headline( 'Rendering incomplete channels' );
$channels	= [
	'missing title'		=> new Channel( '', 'https://example.org/', 'D' ),
	'item w/o content'	=> ( new Channel( 'T', 'https://example.org/', 'D' ) )
		->addItem( new Item( link: 'https://example.org/1' ) ),
	'complete'			=> ( new Channel( 'T', 'https://example.org/', 'D' ) )
		->addItem( new Item( "Control\x0Bcharacters are removed" ) ),
];
foreach( $channels as $label => $channel ){
	try{
		$xml	= Renderer::render( $channel );
		printf( "%-18s  rendered %d bytes\n", $label, strlen( $xml ) );
	}
	catch( RenderException $e ){
		printf( "%-18s  %s\n", $label, $e->getMessage() );
	}
}
