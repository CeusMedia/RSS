# Combining feeds

[`Combiner`](../api/Combiner.md) merges the items of several feeds – RSS and Atom – into one list or feed.

## Adding feeds

```php
use CeusMedia\RSS\Combiner;

$combiner	= ( new Combiner() )
	->add( $rssOrAtomXml, 'https://example.org/feed.rss' )     // XML, URL is optional
	->addChannel( $channel, 'https://example.com/feed.rss' );  // channel model, URL is optional
```

The URL of a feed is used for the `<source>` of its items. Without URL, an `atom:link` with `rel="self"` is used.

### Reading feeds

```php
$errors	= $combiner->addUrls( [
	'https://example.org/feed.rss',
	'https://example.com/feed.atom',
] );
foreach( $errors as $url => $exception )
	error_log( $url.': '.$exception->getMessage() );
```

`addUrls()` reads all feeds in parallel. Feeds which cannot be read or parsed are skipped and returned,
see also `getErrors()`. `addUrl()` reads one feed and throws on failure. To change timeouts or limits,
pass an own [`UrlReader`](url-reader.md) to the constructor: `new Combiner( new UrlReader( timeout: 5 ) )`.

## Combining

```php
$items	= $combiner->combine( 10 );                     // newest 10 items

$planet	= new Channel( 'Planet Example', 'https://planet.example.org/', 'All feeds in one' );
$xml	= $combiner->render( $planet, 10 );            // as feed, $planet is not modified
```

- Items are sorted by publication date, newest first, regardless of their timezone. Items without date are placed last.
- **Duplicates** are removed: items with the same GUID, or the same link if they have no GUID.
  The newest is kept. Disable by `setUnique( FALSE )`.
- **Sources:** items get a `<source>` with title and URL of their original feed, if the URL is known and the
  item has no source yet. Disable by `setAddSource( FALSE )`. The original items are not modified.

## Filters

Only items accepted by all filters are combined. A filter is a closure receiving item and channel:

```php
use CeusMedia\RSS\Filter;
use CeusMedia\RSS\Model\{Channel, Item};

$combiner
	->addFilter( Filter::category( 'Releases', 'Security' ) )      // one of these categories
	->addFilter( Filter::since( new DateTimeImmutable( '-30 days' ) ) )
	->addFilter( Filter::not( Filter::search( 'sponsored' ) ) )
	->addFilter( fn( Item $item, Channel $channel ): bool => 'Example News' !== $channel->getTitle() );
```

| Filter | Keeps items |
|--------|-------------|
| `Filter::category( ...$names )` | having one of the categories, case-insensitive |
| `Filter::search( $text )` | containing the text in title or description (without HTML), case-insensitive |
| `Filter::since( $date )` | published at or after the date; items without date are removed |
| `Filter::not( $filter )` | not accepted by the given filter |
