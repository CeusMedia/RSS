<?php
declare( strict_types=1 );

/**
 *	Demo: Create a podcast feed ready for Apple Podcasts, Spotify and other directories.
 *
 *	Uses the iTunes extension (itunes:*), the Atom extension (atom:link rel="self")
 *	and the Content extension (content:encoded) for show notes.
 *
 *	Usage: php demo/podcast.php
 */

use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;
use CeusMedia\RSS\Extension\ITunes\Category;
use CeusMedia\RSS\Extension\ITunes\ChannelData as PodcastData;
use CeusMedia\RSS\Extension\ITunes\ItemData as EpisodeData;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Renderer;

require_once __DIR__.'/bootstrap.php';

$baseUrl	= 'https://podcast.example.org';

$channel	= ( new Channel( 'Feeds & Formats', $baseUrl.'/', 'A podcast about feeds, formats and the open web.' ) )
	->setLanguage( 'en-us' )
	->setCopyright( '© 2024 Example Inc.' )
	->setLastBuildDate( new DateTimeImmutable( '2024-03-05 08:00:00+01:00' ) )
	//  directories and the W3C validator expect the URL of the feed itself
	->setExtension( AtomData::self( $baseUrl.'/feed.rss' ) )
	->setExtension( ( new PodcastData() )
		->setAuthor( 'Hanna Host' )
		->setOwner( new Person( 'host@podcast.example.org', 'Hanna Host' ) )
		->setImage( $baseUrl.'/artwork-3000.jpg' )				//  1400×1400 to 3000×3000 pixels
		->addCategory( new Category( 'Technology' ) )
		->addCategory( new Category( 'Education', 'How To' ) )
		->setExplicit( FALSE )
		->setType( 'episodic' )
	);

//  episodes: [number, title, date, duration in seconds, file size, show notes]
$episodes	= [
	[2, 'Escaping special characters', '2024-03-05 08:00:00+01:00', 3723, 59_571_200, '<p>Why <code>&lt;</code>, <code>&gt;</code> and <code>&amp;</code> need special care.</p>'],
	[1, 'What is RSS?', '2024-03-01 14:15:00+01:00', 1810, 28_960_000, '<p>A short history of feeds.</p><ul><li>RSS 0.91</li><li>RSS 2.0</li><li>Atom</li></ul>'],
];
foreach( $episodes as [$number, $title, $date, $duration, $size, $notes] ){
	$channel->addItem( ( new Item( 'Episode '.$number.': '.$title, $baseUrl.'/episodes/'.$number ) )
		->setDescription( strip_tags( $notes ) )
		->setPubDate( new DateTimeImmutable( $date ) )
		->setEnclosure( new Enclosure( $baseUrl.'/media/episode-'.$number.'.mp3', $size, 'audio/mpeg' ) )
		->setGuid( new Guid( 'feeds-and-formats-episode-'.$number, FALSE ) )
		->setExtension( new ContentData( $notes ) )
		->setExtension( ( new EpisodeData() )
			->setTitle( $title )									//  without episode number, as recommended by Apple
			->setEpisode( $number )
			->setSeason( 1 )
			->setDuration( $duration )
			->setEpisodeType( 'full' )
		)
	);
}

print Renderer::render( $channel );
