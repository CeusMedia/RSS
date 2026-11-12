<?php
declare( strict_types=1 );

/**
 *	Demo: Create an RSS 2.0 feed from scratch.
 *
 *	Usage: php demo/create.php [--tabs]
 */

use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Model\Source;
use CeusMedia\RSS\Renderer;

require_once __DIR__.'/bootstrap.php';

//  title, link and description are required for every channel
$channel	= new Channel(
	'Ceus Media Demo Feed',
	'https://example.org/',
	'Demonstrates how to create an RSS feed with CeusMedia/RSS'
);
$channel
	->setLanguage( 'en-us' )
	->setCopyright( '© 2024 Example Inc.' )
	->setManagingEditor( new Person( 'editor@example.org', 'Erika Editor' ) )
	->setGenerator( 'CeusMedia/RSS' )
	->setPubDate( new DateTimeImmutable( '2024-03-06 18:00:00+00:00' ) )
	->setLastBuildDate( new DateTimeImmutable( '2024-03-06 18:05:00+00:00' ) )
	->addCategory( 'Technology' )
	->setTtl( 60 )										//  aggregators may cache for 60 minutes
	->setSkipDays( ['Saturday', 'Sunday'] );			//  no updates on weekends

//  title and link of image fall back to channel title and link if not set
$channel->setImage( ( new Image( 'https://example.org/logo.png' ) )->setWidth( 88 )->setHeight( 31 ) );

//  a typical article: the GUID is the URL of the article, so it is a permalink (default)
$channel->addItem( ( new Item( 'Hello World', 'https://example.org/articles/hello-world' ) )
	->setDescription( '<p>HTML content is <em>escaped</em> automatically.</p>' )
	->setAuthor( new Person( 'anna@example.org', 'Anna Author' ) )
	->addCategory( 'General' )
	->addCategory( new Category( 'news/featured', 'https://example.org/categories' ) )
	->setComments( 'https://example.org/articles/hello-world#comments' )
	->setPubDate( new DateTimeImmutable( '2024-03-06 17:00:00+01:00' ) )
	->setGuid( new Guid( 'https://example.org/articles/hello-world' ) )
);

//  a podcast episode: media file as enclosure, opaque GUID (no permalink)
$channel->addItem( ( new Item( 'Podcast episode 1', 'https://example.org/podcast/1' ) )
	->setEnclosure( new Enclosure( 'https://example.org/media/episode-1.mp3', 24986239, 'audio/mpeg' ) )
	->setGuid( new Guid( 'urn:uuid:5f0c1c1e-7c9b-4f4e-9a59-2b0d3c1f0001', FALSE ) )
	->setPubDate( new DateTimeImmutable( '2024-03-05 08:00:00+00:00' ) )
);

//  special characters are escaped, the source references the original feed
$channel->addItem( ( new Item( 'Special characters: < > & " \'', 'https://example.org/articles/special?page=1&lang=en' ) )
	->setSource( new Source( 'Partner Feed', 'https://partner.example.org/rss?format=rss&v=2' ) )
);

//  RSS 2.0 allows items with a description only
$channel->addItem( new Item( description: 'A short note without a title.' ) );

$useTabs	= in_array( '--tabs', $argv ?? [], TRUE );
print Renderer::render( $channel, $useTabs );
