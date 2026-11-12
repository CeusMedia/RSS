# CeusMedia/RSS

![Branch](https://img.shields.io/badge/Branch-0.5.x-blue?style=flat-square)
![Release](https://img.shields.io/badge/Release-0.4.0-blue?style=flat-square)
![PHP version](https://img.shields.io/badge/PHP-%5E8.3-blue?style=flat-square&color=777BB4)
![PHPStan level](https://img.shields.io/badge/PHPStan-max+strict-darkgreen?style=flat-square)
![License](https://img.shields.io/badge/License-GPL--3.0--or--later-lightgrey?style=flat-square)

PHP library to **create, read and combine feeds**: [RSS 2.0](https://www.rssboard.org/rss-specification) and Atom 1.0.

- Supports all elements of the RSS 2.0 specification.
- Reads Atom 1.0 feeds and maps them to the same models.
- Extensions for podcasts (`itunes:*`), full content (`content:encoded`), authors (`dc:creator`)
  and feed links (`atom:link`) – and your own.
- Typed models and immutable value objects, dates as `DateTimeImmutable`.
- Safe output: all values are escaped, characters not allowed in XML are removed.
- Lenient parsing: unknown extensions are ignored, invalid optional values are skipped.
- Secure, robust reading from URLs: TLS verification, timeout, size limit, HTTP(S) only,
  conditional requests (ETag, Last-Modified), parallel downloads.
- Combines several feeds into one: sorted by date, without duplicates, with source references and filters.

> **Upgrading from 0.4?** Version 0.5 changes the API. See [CHANGELOG.md](CHANGELOG.md#upgrading-from-04-to-05).

## Requirements

- PHP 8.3 or newer
- PHP extensions `dom`, `libxml` and `mbstring`
- PHP extension `curl`, only for reading feeds from URLs
- [ceus-media/common](https://github.com/CeusMedia/Common) (installed automatically)

## Installation

```
composer require ceus-media/rss
```

## Quick start

```php
use CeusMedia\RSS\Model\{Channel, Item};
use CeusMedia\RSS\Renderer;

$channel = new Channel('Example News', 'https://example.com/', 'News of example.com');
$channel->addItem(new Item('Hello World', 'https://example.com/hello-world'));

header('Content-Type: application/rss+xml; charset=utf-8');
echo Renderer::render($channel);
```

## Creating a feed

A channel needs a title, a link and a description. Every item needs a title or a description.

```php
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Model\{Category, Channel, Guid, Image, Item, Person};
use CeusMedia\RSS\Renderer;

$channel = (new Channel('Example News', 'https://example.com/', 'News of example.com'))
	->setLanguage('en-us')
	->setManagingEditor(new Person('editor@example.com', 'Erika Editor'))
	->addCategory('Technology')
	->setTtl(60)                                          // aggregators may cache for 60 minutes
	->setImage((new Image('https://example.com/logo.png'))->setWidth(88)->setHeight(31))
	->setExtension(AtomData::self('https://example.com/feed.rss'));   // link to the feed itself

// an article: the GUID is the URL of the article, so it is a permalink (default)
$channel->addItem((new Item('Hello World', 'https://example.com/hello-world'))
	->setDescription('<p>HTML content is escaped automatically.</p>')
	->setAuthor(new Person('anna@example.com', 'Anna Author'))
	->addCategory(new Category('news/featured', 'https://example.com/categories'))
	->setPubDate(new DateTimeImmutable('2024-03-06 17:00:00+01:00'))
	->setGuid(new Guid('https://example.com/hello-world')));

$xml = Renderer::render($channel);          // indented with 2 spaces
$xml = Renderer::render($channel, TRUE);    // indented with tabs
```

If the image has no title or link, the renderer uses the title and link of the channel.
If no `lastBuildDate` is set, the current time is used.

## Podcasts

The iTunes extension adds the elements required by Apple Podcasts, Spotify and other directories.

```php
use CeusMedia\RSS\Extension\ITunes\{Category, ChannelData as PodcastData, ItemData as EpisodeData};
use CeusMedia\RSS\Model\{Enclosure, Guid, Item, Person};

$channel->setExtension((new PodcastData())
	->setAuthor('Hanna Host')
	->setOwner(new Person('host@example.com', 'Hanna Host'))
	->setImage('https://example.com/artwork-3000.jpg')
	->addCategory(new Category('Technology'))
	->setExplicit(FALSE)
	->setType('episodic'));

$channel->addItem((new Item('Episode 1: What is RSS?', 'https://example.com/episodes/1'))
	->setEnclosure(new Enclosure('https://example.com/episode-1.mp3', 28960000, 'audio/mpeg'))
	->setGuid(new Guid('episode-1', isPermaLink: FALSE))
	->setExtension((new EpisodeData())->setEpisode(1)->setDuration(1810)));
```

## Reading a feed

`FeedParser` detects the format and parses RSS 2.0 and Atom 1.0 into the same models.

```php
use CeusMedia\RSS\{FeedParser, UrlReader};
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;

$channel = FeedParser::parse(file_get_contents('feed.xml'));
$channel = FeedParser::parse(UrlReader::read('https://example.org/feed.rss'));

foreach ($channel->getItems() as $item) {
	echo $item->getPubDate()?->format('Y-m-d').'  '.$item->getTitle().PHP_EOL;
	$fullContent = $item->getExtension(ContentData::class)?->encoded;
}
```

Use `Parser` or `AtomParser` directly to accept only one format.
Parsing is lenient, so feeds from the wild can be read:

- Elements of unknown extensions are ignored.
- Invalid optional values are skipped, for example malformed dates or out-of-range image sizes.
- Dates are accepted in RFC 822 format and common deviations (e.g. `GMT`, ISO 8601).
- Leading and trailing whitespace of values is removed.

Atom feeds are mapped like this: `subtitle` → description, `updated` → lastBuildDate,
`summary` → description, `content` → `content:encoded`, `id` → guid, `published` → pubDate,
authors with email → author, author names → `dc:creator`, enclosure links → enclosure.

## Reading from URLs

`UrlReader` uses cURL with secure settings:

| Setting      | Default                                                    |
|--------------|------------------------------------------------------------|
| Protocols    | HTTP and HTTPS only, also for redirects                    |
| TLS          | certificate and host name are verified                     |
| Timeout      | 10 seconds, connect 5 seconds                              |
| Size limit   | 10 MiB                                                     |
| Redirects    | at most 5                                                  |
| Compression  | gzip, deflate (and brotli, if supported by cURL)           |
| User agent   | `CeusMedia-RSS/0.5 (+https://github.com/CeusMedia/RSS)`    |

```php
use CeusMedia\RSS\{FeedParser, UrlReader};
use CeusMedia\RSS\Exception\ReadException;

$reader = new UrlReader(timeout: 5, maxSize: 2_097_152);

// conditional request: unchanged feeds are not downloaded again
$response = $reader->fetch($url, $previousEtag, $previousLastModified);
if (!$response->isNotModified()) {
	$channel = FeedParser::parse($response->content);
	// store $response->etag and $response->lastModified for the next request
}

// several feeds in parallel, failures do not stop other requests
foreach ($reader->fetchAll(['news' => $url1, 'blog' => $url2]) as $key => $result) {
	if ($result instanceof ReadException)
		echo $key.': '.$result->getMessage().PHP_EOL;
}
```

## Combining feeds

```php
use CeusMedia\RSS\{Combiner, Filter};
use CeusMedia\RSS\Model\{Channel, Item};

$combiner = (new Combiner())
	->add($rssOrAtomXml, 'https://example.org/feed.rss')    // URL is used for <source> of items
	->addChannel($channel);

// read feeds in parallel, failing feeds are skipped and reported
$errors = $combiner->addUrls(['https://example.org/feed.rss', 'https://example.com/feed.atom']);

$combiner
	->addFilter(Filter::category('Releases'))
	->addFilter(Filter::since(new DateTimeImmutable('-30 days')))
	->addFilter(fn(Item $item, Channel $channel): bool => NULL !== $item->getLink());

$items = $combiner->combine(10);                         // newest 10 items of all feeds

$target = new Channel('Planet Example', 'https://planet.example.org/', 'All feeds in one');
$xml    = $combiner->render($target, 10);                // as new feed, $target is not modified
```

- Items are sorted by publication date, newest first, regardless of their timezone. Items without date are placed last.
- Duplicates (same GUID, or same link if there is no GUID) are removed, see `setUnique()`.
- Items get a `<source>` referencing their original feed, if its URL is known, see `setAddSource()`.
- Predefined filters: `Filter::category()`, `Filter::search()`, `Filter::since()`, `Filter::not()`.

## Extensions

| Extension     | Prefix   | Data classes                                              | Elements |
|---------------|----------|-----------------------------------------------------------|----------|
| Atom          | `atom`   | `Extension\Atom\ChannelData`, `Link`                      | `atom:link` (e.g. `rel="self"`, WebSub `rel="hub"`) |
| Content       | `content`| `Extension\Content\ItemData`                              | `content:encoded` |
| Dublin Core   | `dc`     | `Extension\DublinCore\Data`                               | `dc:creator` |
| iTunes        | `itunes` | `Extension\ITunes\ChannelData`, `ItemData`, `Category`    | `itunes:*` for podcasts |

Extension data is stored in channels and items:

```php
$channel->setExtension($data);
$data = $channel->getExtension(PodcastData::class);   // typed, NULL if not set
```

Namespaces are only declared in the output if the feed uses them.
Parser and renderer use all built-in extensions by default. To use your own extension,
extend `Extension\AbstractExtension` and register it:

```php
use CeusMedia\RSS\Extension\Extensions;

$extensions = Extensions::default()->add(new MyExtension());
$channel    = FeedParser::parse($xml, $extensions);
$xml        = Renderer::render($channel, FALSE, $extensions);
```

## Models

| Class               | Type                 | Content |
|---------------------|----------------------|---------|
| `Model\Channel`     | model (mutable)      | channel data, items and extension data |
| `Model\Item`        | model (mutable)      | item data and extension data |
| `Model\Image`       | model (mutable)      | channel image, width 1–144, height 1–400 |
| `Model\Person`      | value object         | email and optional name of author, managing editor, web master |
| `Model\Guid`        | value object         | unique identifier of item, permalink by default |
| `Model\Category`    | value object         | category name and optional domain |
| `Model\Enclosure`   | value object         | media file: URL, length in bytes, MIME type |
| `Model\Source`      | value object         | title and URL of the original feed of an item |
| `Model\Cloud`       | value object         | rssCloud settings |
| `Model\TextInput`   | value object         | text input box of channel |
| `Response`          | value object         | response of `UrlReader`: status, content, ETag, Last-Modified |

Value objects are immutable (`readonly`) and validate their values on creation.

## Supported elements

- **RSS 2.0 channel:** `title`, `link`, `description`, `language`, `copyright`, `managingEditor`, `webMaster`,
  `pubDate`, `lastBuildDate`, `category`, `generator`, `docs`, `cloud`, `ttl`, `image`, `rating`,
  `textInput`, `skipHours`, `skipDays`
- **RSS 2.0 item:** `title`, `link`, `description`, `author`, `category`, `comments`, `enclosure`, `guid`,
  `pubDate`, `source`
- **Atom 1.0** (reading): `feed` and `entry` with text constructs (text, html, xhtml), links, persons and categories
- **Extensions:** see above

Not supported: RSS 0.9x and 1.0 (RDF), writing Atom.

## Error handling

| Where                                                   | Exception                                  | Thrown if |
|---------------------------------------------------------|--------------------------------------------|-----------|
| `UrlReader`, `Combiner::addUrl()`                       | `CeusMedia\RSS\Exception\ReadException`    | URL is invalid, request failed or timed out, response too large |
| `FeedParser`, `Parser`, `AtomParser`, `Combiner::add()` | `CeusMedia\RSS\Exception\ParseException`   | input is empty, not well-formed XML or not a supported feed |
| `Renderer::render()`, `Combiner::render()`              | `CeusMedia\RSS\Exception\RenderException`  | channel title, link or description is empty, an item has neither title nor description |
| models and value objects                                | `InvalidArgumentException`                 | a value is invalid, e.g. image width > 144, negative TTL, empty GUID |

`ReadException`, `ParseException` and `RenderException` extend `RuntimeException`.
`ReadException` has the HTTP status or cURL error number as code.

```php
use CeusMedia\RSS\{FeedParser, UrlReader};
use CeusMedia\RSS\Exception\{ParseException, ReadException};

try {
	$channel = FeedParser::parse(UrlReader::read($url));
} catch (ReadException $e) {
	// network problem or HTTP error
} catch (ParseException $e) {
	// not a valid feed
}
```

## Demos

The folder `demo/` contains runnable examples:

| Script             | Content |
|--------------------|---------|
| `demo/create.php`  | Create a feed with image, categories, enclosure and more |
| `demo/read.php`    | Read an RSS or Atom feed from a file or URL: `php demo/read.php https://example.org/feed.rss` |
| `demo/combine.php` | Combine RSS and Atom feeds, newest items first, without duplicates |
| `demo/errors.php`  | Error handling of parser, renderer and models |
| `demo/podcast.php` | Podcast feed for Apple Podcasts and Spotify |
| `demo/planet.php`  | Planet: static HTML page and feed combining several feeds (`--feed` for the feed) |
| `demo/notify.php`  | Notifications for new entries as Slack webhook payloads (`--reset` to start over) |
| `demo/filter.php`  | Filtered feed: only recent releases, or entries containing a search text |

Run them on the command line, for example `php demo/podcast.php`,
or in the browser with `composer dev:demo` and open http://localhost:8080/.

## Development

```
composer install
composer dev:test-all       # syntax check, coding style, PHPStan and unit tests
composer dev:test-units     # unit tests only
composer dev:test-coverage  # unit tests with code coverage (needs Xdebug or PCOV)
composer dev:infection      # mutation tests (needs Xdebug or PCOV)
composer dev:cs             # check coding style, dev:cs-fix to fix it
composer dev:stan           # PHPStan on level max with strict rules
composer dev:rector         # show Rector suggestions for PHP 8.3
composer dev:demo           # web server for demos on http://localhost:8080/
composer dev:docs           # documentation (MkDocs) in doc/Site, see below
composer dev:create-docs    # classic API documentation by DocCreator in doc/API
```

The documentation – guide and API reference – is built with [MkDocs](https://www.mkdocs.org/)
and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material/):

```
composer dev:docs           # generate API reference and build site, open doc/Site/index.html
composer dev:docs-serve     # live preview on http://localhost:8000
composer dev:docs-install   # install MkDocs into a Python venv (optional, else Docker is used)
```

Hand-written pages are in `doc/Manual/`, the API reference is generated from the doc blocks.

Reports are written to `doc/Test/`: coverage in `doc/Test/Coverage/index.html`,
mutation tests in `doc/Test/Infection/index.html`.
Tests of `UrlReader` start local PHP web servers, no internet connection is needed.
GitHub Actions runs all checks on PHP 8.3, 8.4 and 8.5 and builds the documentation, see `.github/workflows/ci.yml`.

| Folder            | Content |
|-------------------|---------|
| `src/`            | library classes |
| `src/Extension/`  | extensions for namespaced elements |
| `test/`           | PHPUnit tests and fixtures |
| `demo/`           | runnable examples and sample feeds |
| `doc/Manual/`     | documentation: guide (hand-written) and API reference (generated) |
| `tool/`           | configuration of PHPUnit, PHPStan, PHP-CS-Fixer, Infection, Rector, MkDocs and DocCreator |

## License

GPL-3.0-or-later, see [gnu.org/licenses/gpl-3.0](https://www.gnu.org/licenses/gpl-3.0.txt).
Copyright © 2012–2026 Christian Würker, [Ceus Media](https://ceusmedia.de/).
