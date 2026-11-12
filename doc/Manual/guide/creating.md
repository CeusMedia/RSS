# Creating feeds

## Channel

A channel needs a **title**, a **link** to the website and a **description**:

```php
use CeusMedia\RSS\Model\{Channel, Person};

$channel	= ( new Channel( 'Example News', 'https://example.com/', 'News of example.com' ) )
	->setLanguage( 'en-us' )
	->setCopyright( '© 2024 Example Inc.' )
	->setManagingEditor( new Person( 'editor@example.com', 'Erika Editor' ) )
	->setWebMaster( new Person( 'webmaster@example.com' ) )
	->setGenerator( 'My CMS' )
	->addCategory( 'Technology' )
	->setTtl( 60 );                                   // aggregators may cache for 60 minutes
```

All setters return the model, so calls can be chained.

| Element | Method | Notes |
|---------|--------|-------|
| `pubDate` | `setPubDate( DateTimeInterface )` | publication date of the content |
| `lastBuildDate` | `setLastBuildDate( DateTimeInterface )` | current time if not set |
| `docs` | `setDocs( ?string )` | link to the specification by default, `NULL` to omit |
| `cloud` | `setCloud( new Cloud( … ) )` | rssCloud notifications |
| `skipHours`, `skipDays` | `setSkipHours( [0, 1] )`, `setSkipDays( ['Saturday'] )` | validated, sorted, unique |
| `textInput`, `rating` | `setTextInput( new TextInput( … ) )`, `setRating( … )` | rarely used |

Dates keep their timezone: `2024-03-06 17:00:00+01:00` is rendered as `Wed, 06 Mar 2024 17:00:00 +0100`.

## Image

```php
use CeusMedia\RSS\Model\Image;

$channel->setImage( ( new Image( 'https://example.com/logo.png' ) )->setWidth( 88 )->setHeight( 31 ) );
```

Title and link of the image are required by the specification – if not set, the title and link of the channel
are used. Width must be 1–144, height 1–400 pixels.

## Items

Each item needs a **title** or a **description**:

```php
use CeusMedia\RSS\Model\{Category, Enclosure, Guid, Item, Person, Source};

$channel->addItem( ( new Item( 'Hello World', 'https://example.com/hello-world' ) )
	->setDescription( '<p>The description may contain <em>HTML</em>.</p>' )
	->setAuthor( new Person( 'anna@example.com', 'Anna Author' ) )
	->addCategory( 'News' )
	->addCategory( new Category( 'featured', 'https://example.com/tags' ) )
	->setComments( 'https://example.com/hello-world#comments' )
	->setPubDate( new DateTimeImmutable( '2024-03-06 17:00' ) )
	->setGuid( new Guid( 'https://example.com/hello-world' ) )
	->setSource( new Source( 'Partner Feed', 'https://partner.example.com/rss' ) ) );

$channel->addItem( new Item( description: 'A short note without title.' ) );
```

### GUID

The GUID identifies an item, so aggregators do not show it twice. It is a **permalink by default** –
a URL pointing to the item. For other identifiers, disable it:

```php
new Guid( 'https://example.com/hello-world' );                         // isPermaLink="true"
new Guid( 'urn:uuid:5f0c1c1e-7c9b-4f4e-9a59-2b0d3c1f0001', FALSE );   // isPermaLink="false"
```

### Enclosure

An enclosure attaches a media file, like an episode of a podcast. Length in bytes and MIME type are required
by the specification, the length may be `0` if unknown:

```php
$item->setEnclosure( new Enclosure( 'https://example.com/episode-1.mp3', 28960000, 'audio/mpeg' ) );
```

See [Podcasts](podcasts.md) for everything else needed for podcasts.

## Rendering

```php
use CeusMedia\RSS\Renderer;

$xml	= Renderer::render( $channel );            // indented with 2 spaces
$xml	= Renderer::render( $channel, TRUE );      // indented with tabs
```

- All values are escaped, HTML in descriptions included – readers decode it again.
- Characters not allowed in XML (like `\x0B`) are removed, invalid UTF-8 is replaced by U+FFFD.
- Empty optional values are not rendered.
- Missing required values throw a [`RenderException`](errors.md).

!!! tip "Link to the feed itself"
    The W3C feed validator recommends an `atom:link` with `rel="self"`:

    ```php
    use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;

    $channel->setExtension( AtomData::self( 'https://example.com/feed.rss' ) );
    ```
