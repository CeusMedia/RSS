# Installation

## Requirements

| Requirement | Purpose |
|-------------|---------|
| PHP **8.3** or newer | |
| `ext-dom`, `ext-libxml` | parsing and rendering XML |
| `ext-mbstring` | case-insensitive filters |
| `ext-curl` | only for [reading feeds from URLs](url-reader.md) |
| `ceus-media/common` | installed by Composer automatically |

## Composer

```sh
composer require ceus-media/rss
```

The library follows PSR-4, all classes live in namespace `CeusMedia\RSS`.
Include Composer's autoloader and you are ready:

```php
require_once 'vendor/autoload.php';

use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Renderer;

print Renderer::render( new Channel( 'Title', 'https://example.com/', 'Description' ) );
```

## Overview

| Class | Purpose |
|-------|---------|
| [`Model\Channel`](../api/Model/Channel.md), [`Model\Item`](../api/Model/Item.md) | the feed and its entries |
| [`Renderer`](../api/Renderer.md) | renders a channel to RSS 2.0 |
| [`FeedParser`](../api/FeedParser.md) | parses RSS 2.0 or Atom 1.0, detecting the format |
| [`UrlReader`](../api/UrlReader.md) | reads feeds from URLs, securely and in parallel |
| [`Combiner`](../api/Combiner.md), [`Filter`](../api/Filter.md) | combines several feeds into one |
| [`Extension\…`](extensions.md) | namespaced elements: Atom links, full content, Dublin Core, iTunes |

## Upgrading from 0.4

Version 0.5 changes the API. The most important changes:

- Element names follow the specification: `setPubDate()` instead of `setDate()`, `setGuid()` instead of `setId()`,
  `setDescription()` instead of `setContent()`, `setManagingEditor()` and `setWebMaster()` instead of `setManager()` and `setAdmin()`.
- Persons, GUIDs, sources, categories and cloud settings are value objects instead of arrays.
- Dates are `DateTimeImmutable` instead of UNIX timestamps.
- **A GUID is a permalink by default**, as defined by the specification: use `new Guid( $id, isPermaLink: FALSE )`
  for identifiers which are not URLs.
- `Renderer` throws `RenderException`, parsers throw `ParseException` – both extend `RuntimeException`.

See the [changelog](../changelog.md#upgrading-from-04-to-05) for tables of all renamed methods.
