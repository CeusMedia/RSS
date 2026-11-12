# Use cases and recipes

All recipes are runnable demos in folder `demo/` – run them by `php demo/<name>.php` or in the browser
by `composer dev:demo`.

## Podcast feed

Publish a podcast for Apple Podcasts, Spotify and other directories: artwork, categories, episodes with
enclosure, duration and show notes. See [Podcasts](podcasts.md) and `demo/podcast.php`.

## Planet: one page for many feeds

Collect the feeds of a team, project or community into one page and one feed, e.g. by a cron job:

```php
use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Model\Channel;

$combiner	= new Combiner();
$errors		= $combiner->addUrls( $feedUrls );                 // in parallel, failing feeds are skipped

$planet		= ( new Channel( 'Planet Example', 'https://planet.example.org/', 'All feeds in one' ) )
	->setExtension( AtomData::self( 'https://planet.example.org/feed.rss' ) );
file_put_contents( 'public/feed.rss', $combiner->render( $planet, 50 ) );

foreach( $combiner->combine( 50 ) as $item ){
	//  render HTML: escape everything, strip HTML of descriptions – content of feeds is not trusted
	printf( '<a href="%s">%s</a> from %s',
		htmlspecialchars( $item->getLink() ?? '#' ),
		htmlspecialchars( $item->getTitle() ?? '' ),
		htmlspecialchars( $item->getSource()?->title ?? '' ) );
}
```

See `demo/planet.php`.

!!! warning "Do not trust content of feeds"
    Descriptions and content of foreign feeds may contain any HTML, including scripts.
    Strip or sanitize it before showing it on a web page.

## Notifications for new entries

Post new entries of feeds to Slack, Matrix or Teams: remember the GUIDs of seen entries, and use conditional
requests, so unchanged feeds are not downloaded again:

```php
$response	= ( new UrlReader() )->fetch( $url, $state['etag'] ?? NULL, $state['lastModified'] ?? NULL );
if( !$response->isNotModified() ){
	foreach( FeedParser::parse( $response->content )->getItems() as $item ){
		$id	= $item->getGuid()->value ?? $item->getLink();
		if( NULL === $id || isset( $state['seen'][$id] ) )
			continue;
		$state['seen'][$id]	= TRUE;
		postToWebhook( ['text' => $item->getTitle().' '.$item->getLink()] );
	}
	$state['etag']			= $response->etag;
	$state['lastModified']	= $response->lastModified;
}
```

See `demo/notify.php`.

## Filtered feed

Offer a feed of only some entries – e.g. only releases of the last weeks, from several feeds:

```php
$combiner->addFilter( Filter::category( 'Releases' ) )
	->addFilter( Filter::since( new DateTimeImmutable( '-60 days' ) ) );
print $combiner->render( new Channel( 'Releases', 'https://example.org/releases', 'All releases' ) );
```

See `demo/filter.php`.

## Checking own feeds

Parse your own feeds in tests or in a deployment pipeline: a `ParseException` shows broken XML,
the models show missing values.

```php
$channel	= FeedParser::parse( UrlReader::read( 'https://example.com/feed.rss' ) );
assert( '' !== $channel->getTitle() && [] !== $channel->getItems() );
```
