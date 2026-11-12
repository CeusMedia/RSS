<?php
declare( strict_types=1 );

/**
 *	Demo: Filtered feed – only releases of the last weeks, from several feeds.
 *
 *	Usage: php demo/filter.php [search text]
 */

use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Filter;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;

require_once __DIR__.'/bootstrap.php';

$combiner	= ( new Combiner() )
	->add( (string) file_get_contents( __DIR__.'/data/news.rss' ), 'https://news.example.org/feed.rss' )
	->add( (string) file_get_contents( __DIR__.'/data/releases.atom' ), 'https://code.example.org/releases.atom' );

$search	= $argv[1] ?? NULL;
if( NULL !== $search ){
	headline( 'Entries containing "'.$search.'"' );
	$combiner->addFilter( Filter::search( $search ) );
}
else{
	headline( 'Releases since 2024-02-01' );
	$combiner
		->addFilter( Filter::category( 'Releases' ) )
		->addFilter( Filter::since( new DateTimeImmutable( '2024-02-01' ) ) );
}

//  custom filters are closures receiving item and channel
$combiner->addFilter( static fn( Item $item, Channel $channel ): bool => NULL !== $item->getLink() );

foreach( $combiner->combine() as $item )
	printf( "%s  %-30s  %s\n", formatDate( $item->getPubDate() ), $item->getTitle(), $item->getSource()?->title );

headline( 'As feed' );
print $combiner->render( new Channel( 'Example Releases', 'https://example.org/releases', 'All releases of example.org' ) );
