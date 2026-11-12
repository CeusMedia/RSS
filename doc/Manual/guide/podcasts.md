# Podcasts

Podcast directories like Apple Podcasts and Spotify read RSS feeds with an `enclosure` per episode and
additional elements of the iTunes namespace. The iTunes extension provides them.

## Channel

```php
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Extension\ITunes\{Category, ChannelData as PodcastData};
use CeusMedia\RSS\Model\{Channel, Person};

$channel	= ( new Channel( 'Feeds & Formats', 'https://podcast.example.org/', 'A podcast about feeds.' ) )
	->setLanguage( 'en-us' )
	->setExtension( AtomData::self( 'https://podcast.example.org/feed.rss' ) )
	->setExtension( ( new PodcastData() )
		->setAuthor( 'Hanna Host' )
		->setOwner( new Person( 'host@podcast.example.org', 'Hanna Host' ) )
		->setImage( 'https://podcast.example.org/artwork-3000.jpg' )
		->addCategory( new Category( 'Technology' ) )
		->addCategory( new Category( 'Education', 'How To' ) )
		->setExplicit( FALSE )
		->setType( 'episodic' ) );
```

| Method | Element | Notes |
|--------|---------|-------|
| `setImage()` | `itunes:image` | artwork, JPEG or PNG, 1400×1400 to 3000×3000 pixels |
| `addCategory()` | `itunes:category` | [Apple Podcasts categories](https://podcasters.apple.com/support/1691-apple-podcasts-categories), with optional subcategory |
| `setExplicit()` | `itunes:explicit` | `TRUE` or `FALSE` |
| `setOwner()` | `itunes:owner` | contact for directories, not shown publicly |
| `setType()` | `itunes:type` | `episodic` (newest first) or `serial` (oldest first) |
| `setNewFeedUrl()` | `itunes:new-feed-url` | after moving the feed |
| `setBlock()`, `setComplete()` | `itunes:block`, `itunes:complete` | hide podcast, no more episodes |

## Episodes

```php
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;
use CeusMedia\RSS\Extension\ITunes\ItemData as EpisodeData;
use CeusMedia\RSS\Model\{Enclosure, Guid, Item};

$channel->addItem( ( new Item( 'Episode 1: What is RSS?', 'https://podcast.example.org/episodes/1' ) )
	->setDescription( 'A short history of feeds.' )
	->setPubDate( new DateTimeImmutable( '2024-03-01 14:15:00+01:00' ) )
	->setEnclosure( new Enclosure( 'https://podcast.example.org/media/episode-1.mp3', 28960000, 'audio/mpeg' ) )
	->setGuid( new Guid( 'feeds-and-formats-episode-1', FALSE ) )
	->setExtension( new ContentData( '<p>Show notes with <a href="https://example.org/">links</a>.</p>' ) )
	->setExtension( ( new EpisodeData() )
		->setTitle( 'What is RSS?' )               // without numbers, as recommended by Apple
		->setEpisode( 1 )
		->setSeason( 1 )
		->setDuration( 1810 )                      // seconds
		->setEpisodeType( 'full' ) ) );
```

!!! warning "Keep GUIDs stable"
    Directories identify episodes by their GUID. Never change it, or episodes are downloaded again.
    Use an identifier which does not depend on the URL of the file.

## Reading podcasts

When parsing, durations given as `HH:MM:SS` or `MM:SS` are converted to seconds and `yes`/`no`/`clean`
for explicit are understood:

```php
use CeusMedia\RSS\Extension\ITunes\ItemData as EpisodeData;
use CeusMedia\RSS\FeedParser;

foreach( FeedParser::parse( $xml )->getItems() as $item ){
	$episode	= $item->getExtension( EpisodeData::class );
	printf( "%s (%d min)\n", $item->getTitle(), intdiv( $episode?->getDuration() ?? 0, 60 ) );
}
```

See `demo/podcast.php` for a complete example.
