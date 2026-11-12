```php
use CeusMedia\RSS\Model\{Channel, Image, Item, Person};

$channel	= ( new Channel( 'Example News', 'https://example.com/', 'News of example.com' ) )
	->setLanguage( 'en-us' )
	->setManagingEditor( new Person( 'editor@example.com', 'Erika Editor' ) )
	->addCategory( 'Technology' )
	->setTtl( 60 )
	->setImage( new Image( 'https://example.com/logo.png' ) )
	->addItem( new Item( 'Hello World', 'https://example.com/hello-world' ) );
```

See guide: [Creating feeds](../../guide/creating.md#channel)
