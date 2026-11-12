```php
use CeusMedia\RSS\Model\{Enclosure, Guid, Item, Person};

$item	= ( new Item( 'Hello World', 'https://example.com/hello-world' ) )
	->setDescription( '<p>HTML is escaped automatically.</p>' )
	->setAuthor( new Person( 'anna@example.com', 'Anna Author' ) )
	->addCategory( 'News' )
	->setPubDate( new DateTimeImmutable( '2024-03-06 17:00' ) )
	->setGuid( new Guid( 'https://example.com/hello-world' ) )
	->setEnclosure( new Enclosure( 'https://example.com/audio.mp3', 1048576, 'audio/mpeg' ) );
```

See guide: [Items](../../guide/creating.md#items)
