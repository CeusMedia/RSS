# Quickstart

## Publish a feed

```php
use CeusMedia\RSS\Model\{Channel, Guid, Item};
use CeusMedia\RSS\Renderer;

$channel	= new Channel( 'Example News', 'https://example.com/', 'News of example.com' );

foreach( $articles as $article ){
	$channel->addItem( ( new Item( $article->title, $article->url ) )
		->setDescription( $article->teaser )
		->setPubDate( $article->publishedAt )
		->setGuid( new Guid( $article->url ) ) );
}

header( 'Content-Type: application/rss+xml; charset=utf-8' );
print Renderer::render( $channel );
```

Title, link and description are required for the channel, title or description for each item.
Everything else is optional.

## Read a feed

```php
use CeusMedia\RSS\{FeedParser, UrlReader};

$channel	= FeedParser::parse( UrlReader::read( 'https://example.org/feed.rss' ) );

print $channel->getTitle().PHP_EOL;
foreach( $channel->getItems() as $item )
	printf( "%s  %s\n", $item->getPubDate()?->format( 'Y-m-d' ) ?? '-', $item->getTitle() );
```

`FeedParser` reads RSS 2.0 and Atom 1.0. `UrlReader` verifies TLS certificates, times out after 10 seconds
and stops at 10 MiB.

## Combine feeds

```php
use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Model\Channel;

$combiner	= new Combiner();
$errors		= $combiner->addUrls( [
	'https://example.org/feed.rss',
	'https://example.com/feed.atom',
] );

$planet	= new Channel( 'Planet Example', 'https://planet.example.org/', 'All feeds in one' );
print $combiner->render( $planet, 20 );
```

The newest 20 items of all feeds, without duplicates, each with a `<source>` linking its original feed.

## Next steps

- [Creating feeds](creating.md) – all elements, images, categories, enclosures
- [Podcasts](podcasts.md) – feeds for Apple Podcasts and Spotify
- [Recipes](recipes.md) – planet page, notifications, filtered feeds
