---
title: Home
hide:
  - navigation
  - toc
---

<div class="hero" markdown>

# CeusMedia RSS

Create, read and combine feeds with PHP: **RSS 2.0** and **Atom 1.0** in typed models,
with podcast support, safe output and secure, parallel downloads.

[Get started](guide/quickstart.md){ .md-button .md-button--primary }
[Podcasts](guide/podcasts.md){ .md-button }
[API Reference](api/index.md){ .md-button }
[GitHub](https://github.com/CeusMedia/RSS){ .md-button }

</div>

=== "Create a feed"

    ```php
    use CeusMedia\RSS\Model\{Channel, Guid, Item};
    use CeusMedia\RSS\Renderer;

    $channel	= new Channel( 'Example News', 'https://example.com/', 'News of example.com' );
    $channel->addItem( ( new Item( 'Hello World', 'https://example.com/hello-world' ) )
    	->setDescription( '<p>HTML is escaped automatically.</p>' )
    	->setPubDate( new DateTimeImmutable( '2024-03-06 17:00' ) )
    	->setGuid( new Guid( 'https://example.com/hello-world' ) ) );

    header( 'Content-Type: application/rss+xml; charset=utf-8' );
    print Renderer::render( $channel );
    ```

=== "Read RSS or Atom"

    ```php
    use CeusMedia\RSS\{FeedParser, UrlReader};

    $channel	= FeedParser::parse( UrlReader::read( 'https://example.org/feed.atom' ) );
    foreach( $channel->getItems() as $item )
    	print $item->getPubDate()?->format( 'Y-m-d' ).'  '.$item->getTitle().PHP_EOL;
    ```

=== "Combine feeds"

    ```php
    use CeusMedia\RSS\{Combiner, Filter};
    use CeusMedia\RSS\Model\Channel;

    $combiner	= new Combiner();
    $errors		= $combiner->addUrls( ['https://example.org/feed.rss', 'https://example.com/feed.atom'] );
    $combiner->addFilter( Filter::since( new DateTimeImmutable( '-30 days' ) ) );

    $planet		= new Channel( 'Planet Example', 'https://planet.example.org/', 'All feeds in one' );
    print $combiner->render( $planet, 20 );     // newest 20 items, without duplicates
    ```

## Features

<div class="grid cards" markdown>

-   :material-rss:{ .lg .middle } __Complete RSS 2.0__

    ---

    All elements of the specification – enclosures, categories with domain, cloud, TTL, skip hours and days – in typed models.

    [:octicons-arrow-right-24: Creating feeds](guide/creating.md)

-   :material-podcast:{ .lg .middle } __Podcasts__

    ---

    The iTunes extension adds artwork, categories, episodes, seasons and durations required by Apple Podcasts and Spotify.

    [:octicons-arrow-right-24: Podcasts](guide/podcasts.md)

-   :material-file-document-multiple-outline:{ .lg .middle } __RSS and Atom__

    ---

    `FeedParser` detects the format and maps Atom feeds to the same models. Parsing is lenient, so feeds from the wild can be read.

    [:octicons-arrow-right-24: Reading feeds](guide/reading.md)

-   :material-shield-lock-outline:{ .lg .middle } __Secure downloads__

    ---

    TLS verification, timeouts, size limit and HTTP(S) only by default – plus conditional requests and parallel downloads.

    [:octicons-arrow-right-24: Reading from URLs](guide/url-reader.md)

-   :material-call-merge:{ .lg .middle } __Combining feeds__

    ---

    Merge feeds sorted by date, remove duplicates, reference the original feed and filter by category, date or text.

    [:octicons-arrow-right-24: Combining feeds](guide/combining.md)

-   :material-puzzle-outline:{ .lg .middle } __Extensions__

    ---

    `atom:link`, `content:encoded`, `dc:creator`, `itunes:*` built in – and your own namespaces by a small class.

    [:octicons-arrow-right-24: Extensions](guide/extensions.md)

</div>

## Safe by default

- **Output** is built with DOM: all values and attributes are escaped, characters not allowed in XML are removed
  and invalid UTF-8 is replaced – a broken feed is not possible.
- **Input** is parsed without loading external entities or accessing the network.
- **Downloads** verify TLS certificates, allow only HTTP and HTTPS (also for redirects), time out and stop at 10 MiB.
- **Errors** raise exceptions: `ReadException`, `ParseException` and `RenderException` – no silent empty feeds.

## More

<div class="grid cards" markdown>

-   :material-book-open-variant:{ .lg .middle } __Recipes__

    ---

    A planet page, notifications for new entries, filtered feeds and a podcast – ready to copy.

    [:octicons-arrow-right-24: Use cases and recipes](guide/recipes.md)

-   :material-alert-circle-outline:{ .lg .middle } __Error handling__

    ---

    Which exception is thrown when, and how invalid values in feeds are handled.

    [:octicons-arrow-right-24: Error handling](guide/errors.md)

-   :material-api:{ .lg .middle } __API reference__

    ---

    Every class and method, generated from the code.

    [:octicons-arrow-right-24: API reference](api/index.md)

</div>

!!! info "New in version 0.5"
    Version 0.5 is a rewrite with typed models, value objects, Atom support, extensions and a secure URL reader.
    It changes the API – see [Installation](guide/installation.md#upgrading-from-04) and the [changelog](changelog.md).

## Requirements

- PHP 8.3 or newer
- Extensions `dom`, `libxml` and `mbstring`, and `curl` for reading feeds from URLs

```sh
composer require ceus-media/rss
```

License: [GPL-3.0-or-later](https://www.gnu.org/licenses/gpl-3.0.txt)
