```php
use CeusMedia\RSS\FeedParser;

$channel	= FeedParser::parse( $xml );                  // RSS 2.0 or Atom 1.0
$format		= FeedParser::detect( $xml );                 // FeedParser::FORMAT_RSS or FORMAT_ATOM
```

See guide: [Reading feeds](../guide/reading.md)
