# Reading feeds

## Parsers

| Class | Reads |
|-------|-------|
| [`FeedParser`](../api/FeedParser.md) | RSS 2.0 and Atom 1.0, detecting the format |
| [`Parser`](../api/Parser.md) | RSS 2.0 only |
| [`AtomParser`](../api/AtomParser.md) | Atom 1.0 only |

```php
use CeusMedia\RSS\FeedParser;

$channel	= FeedParser::parse( file_get_contents( 'feed.xml' ) );
$format		= FeedParser::detect( $xml );        // FeedParser::FORMAT_RSS or FORMAT_ATOM
```

All parsers return a [`Model\Channel`](../api/Model/Channel.md) with its items – the same models used for
[creating feeds](creating.md), so a parsed feed can be modified and rendered again.

## Lenient parsing

Feeds in the wild are not always valid. As long as the XML is well-formed, the parsers read what they can:

- Elements of **unknown namespaces** are ignored, elements of [registered extensions](extensions.md) are parsed.
  Extension elements like `media:title` cannot overwrite RSS elements.
- **Invalid optional values** are skipped: a malformed date, a non-numeric TTL, an image wider than 144 pixels,
  a cloud without valid port, an enclosure without URL.
- **Dates** are accepted in RFC 822 format and common deviations, like `GMT` instead of `+0000`,
  missing weekday or ISO 8601.
- **Whitespace** around values is removed.
- **CDATA** sections are read like text.

Required values which are missing are returned as empty string (title, link and description of a channel)
or `NULL` (all optional values).

## Atom feeds

Atom feeds are mapped to the RSS models:

| Atom | RSS model |
|------|-----------|
| `feed/title`, `subtitle` | title, description |
| `feed/link` (alternate) | link |
| `feed/link` (other relations, like `self`) | [`Extension\Atom\ChannelData`](../api/Extension/Atom/ChannelData.md) |
| `feed/updated` | lastBuildDate |
| `feed/rights`, `generator`, `category` | copyright, generator, categories |
| `feed/logo` or `icon` | image |
| `author` with email | managing editor (feed), author (entry) |
| `author` names | `dc:creator` ([`Extension\DublinCore\Data`](../api/Extension/DublinCore/Data.md)) |
| `entry/summary` (or `content`) | description |
| `entry/content` | `content:encoded` ([`Extension\Content\ItemData`](../api/Extension/Content/ItemData.md)) |
| `entry/id` | GUID, permalink if equal to the link |
| `entry/published` (or `updated`) | pubDate |
| `entry/link` (enclosure) | enclosure |

Text constructs of type `text`, `html` and `xhtml` are supported. For `xhtml`, the inner markup of the
wrapping `div` is returned.

## Security

The XML is loaded without resolving external entities and without network access, so XXE attacks are not possible.
Invalid XML throws a [`ParseException`](errors.md) containing all errors reported by libxml.
