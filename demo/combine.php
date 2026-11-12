<?php
declare( strict_types=1 );

/**
 *	Demo: Combine several feeds into one feed, newest items first.
 *
 *	Usage: php demo/combine.php [limit]
 *	Default limit: 5
 */

use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Model\Channel;

require_once __DIR__.'/bootstrap.php';

$limit		= (int) ( $argv[1] ?? 5 );

$combiner	= new Combiner();
//  RSS and Atom feeds can be combined, the URL is used for the <source> of items
$combiner->add( (string) file_get_contents( __DIR__.'/data/news.rss' ), 'https://news.example.org/feed.rss' );
$combiner->add( (string) file_get_contents( __DIR__.'/data/podcast.rss' ), 'https://podcast.example.org/feed.rss' );
$combiner->add( (string) file_get_contents( __DIR__.'/data/releases.atom' ), 'https://code.example.org/releases.atom' );
//  remote feeds are read in parallel, failing feeds are skipped and reported:
//  $errors = $combiner->addUrls( ['https://example.org/feed.rss', 'https://example.com/feed.atom'] );

//  items are sorted by publication date, regardless of their timezone, duplicates are removed
headline( 'Combined items (limit '.$limit.')' );
foreach( $combiner->combine( $limit ) as $item )
	printf( "%s  %-42s  %s\n", formatDate( $item->getPubDate() ), $item->getTitle() ?? $item->getDescription() ?? '', $item->getSource()?->title );

//  the target channel provides title, link and description of the combined feed
//  it is not modified by render(), so it can be reused
$target	= new Channel(
	'Example Planet',
	'https://planet.example.org/',
	'News and podcast episodes of example.org in one feed'
);

headline( 'Combined feed' );
print $combiner->render( $target, $limit );
