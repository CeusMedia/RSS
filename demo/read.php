<?php
declare( strict_types=1 );

/**
 *	Demo: Read an RSS or Atom feed from a file or URL and print its contents.
 *
 *	Usage: php demo/read.php [file-or-url]
 *	Default: demo/data/podcast.rss
 */

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Exception\ReadException;
use CeusMedia\RSS\Extension\ITunes\ItemData as EpisodeData;
use CeusMedia\RSS\FeedParser;
use CeusMedia\RSS\UrlReader;

require_once __DIR__.'/bootstrap.php';

$source		= $argv[1] ?? __DIR__.'/data/podcast.rss';
$isUrl		= 1 === preg_match( '@^https?://@', $source );
if( !$isUrl && !is_readable( $source ) )
	fail( 'File not found or not readable: '.$source );

try{
	//  UrlReader verifies TLS certificates, has a timeout and allows HTTP(S) only
	$xml		= $isUrl ? UrlReader::read( $source ) : (string) file_get_contents( $source );
	$channel	= FeedParser::parse( $xml );
}
catch( ParseException $e ){
	fail( 'Not a valid RSS feed: '.$e->getMessage() );
}
catch( ReadException $e ){
	fail( $e->getMessage() );
}

headline( 'Channel: '.$channel->getTitle() );
printf( "Link:         %s\n", $channel->getLink() );
printf( "Description:  %s\n", $channel->getDescription() );
printf( "Language:     %s\n", $channel->getLanguage() ?? '-' );
printf( "Categories:   %s\n", [] !== $channel->getCategories() ? implode( ', ', $channel->getCategories() ) : '-' );
printf( "Last build:   %s\n", formatDate( $channel->getLastBuildDate() ) );
if( NULL !== $channel->getTtl() )
	printf( "Cache for:    %d minutes\n", $channel->getTtl() );
if( NULL !== $channel->getManagingEditor() )
	printf( "Editor:       %s\n", $channel->getManagingEditor() );
$image	= $channel->getImage();
if( NULL !== $image )
	printf( "Image:        %s (%sx%s)\n", $image->getUrl(), $image->getWidth() ?? 88, $image->getHeight() ?? 31 );

headline( count( $channel->getItems() ).' items' );
foreach( $channel->getItems() as $nr => $item ){
	printf( "#%d  %s\n", $nr + 1, $item->getTitle() ?? '(untitled)' );
	printf( "    Date:   %s\n", formatDate( $item->getPubDate() ) );
	if( NULL !== $item->getLink() )
		printf( "    Link:   %s\n", $item->getLink() );
	if( NULL !== $item->getAuthor() )
		printf( "    Author: %s\n", $item->getAuthor()->name ?? $item->getAuthor()->email );
	if( [] !== $item->getCategories() )
		printf( "    Tags:   %s\n", implode( ', ', $item->getCategories() ) );
	$enclosure	= $item->getEnclosure();
	if( NULL !== $enclosure )
		printf( "    Media:  %s (%s, %s)\n", $enclosure->url, $enclosure->type, UnitFormater::formatBytes( $enclosure->length ) );
	$duration	= $item->getExtension( EpisodeData::class )?->getDuration();
	if( NULL !== $duration )
		printf( "    Length: %d:%02d min\n", intdiv( $duration, 60 ), $duration % 60 );
	$guid	= $item->getGuid();
	if( NULL !== $guid )
		printf( "    GUID:   %s%s\n", $guid, $guid->isPermaLink ? ' (permalink)' : '' );
	if( NULL !== $item->getDescription() )
		printf( "    Text:   %s\n", mb_strimwidth( strip_tags( $item->getDescription() ), 0, 70, '…' ) );
	print PHP_EOL;
}
