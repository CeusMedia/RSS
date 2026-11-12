```php
use CeusMedia\RSS\Extension\Extensions;

$extensions	= Extensions::default()->add( new MyExtension() );
$channel	= FeedParser::parse( $xml, $extensions );
$xml		= Renderer::render( $channel, FALSE, $extensions );

$channel	= FeedParser::parse( $xml, Extensions::none() );      // ignore all extension elements
```

See guide: [Extensions](../../guide/extensions.md)
