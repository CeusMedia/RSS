# Error handling

## Exceptions

| Where | Exception | Thrown if |
|-------|-----------|-----------|
| `UrlReader`, `Combiner::addUrl()` | [`ReadException`](../api/Exception/ReadException.md) | URL is invalid, request failed or timed out, response too large, HTTP error |
| `FeedParser`, `Parser`, `AtomParser`, `Combiner::add()` | [`ParseException`](../api/Exception/ParseException.md) | input is empty, not well-formed XML or not a supported feed |
| `Renderer::render()`, `Combiner::render()` | [`RenderException`](../api/Exception/RenderException.md) | channel title, link or description is empty, an item has neither title nor description |
| models and value objects | `InvalidArgumentException` | a value is invalid, e.g. image width > 144, negative TTL, empty GUID |

`ReadException`, `ParseException` and `RenderException` extend `RuntimeException`.

```php
use CeusMedia\RSS\{FeedParser, UrlReader};
use CeusMedia\RSS\Exception\{ParseException, ReadException};

try{
	$channel	= FeedParser::parse( UrlReader::read( $url ) );
}
catch( ReadException $e ){
	//  network problem or HTTP error, $e->getCode() is the HTTP status or cURL error number
}
catch( ParseException $e ){
	//  not a valid feed, the message contains the XML errors
}
```

## Validation of values

Models and value objects validate values on creation, so invalid feeds cannot be built:

| Value | Rule |
|-------|------|
| `Person`, `Guid`, `Category`, `Source`, `Enclosure`, `Cloud`, `TextInput` | required values must not be empty |
| `Image::setWidth()`, `setHeight()` | 1–144 and 1–400 pixels |
| `Channel::setTtl()` | not negative |
| `Channel::setSkipHours()`, `setSkipDays()` | hours 0–23, English day names |
| `Cloud` port | 1–65535 |
| `Enclosure` length | not negative |
| iTunes type, episode type, episode, season, duration | see [`ITunes\ChannelData`](../api/Extension/ITunes/ChannelData.md), [`ITunes\ItemData`](../api/Extension/ITunes/ItemData.md) |

When **parsing**, invalid optional values do not throw – they are skipped, see [Reading feeds](reading.md#lenient-parsing).
