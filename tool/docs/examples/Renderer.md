```php
use CeusMedia\RSS\Model\{Channel, Item};
use CeusMedia\RSS\Renderer;

$channel	= new Channel( 'Example News', 'https://example.com/', 'News of example.com' );
$channel->addItem( new Item( 'Hello World', 'https://example.com/hello-world' ) );

$xml	= Renderer::render( $channel );            // indented with 2 spaces
$xml	= Renderer::render( $channel, TRUE );      // indented with tabs
```

See guide: [Creating feeds](../guide/creating.md#rendering)
