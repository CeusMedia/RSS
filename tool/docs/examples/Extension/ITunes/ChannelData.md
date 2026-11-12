```php
use CeusMedia\RSS\Extension\ITunes\{Category, ChannelData as PodcastData};
use CeusMedia\RSS\Model\Person;

$channel->setExtension( ( new PodcastData() )
	->setAuthor( 'Hanna Host' )
	->setOwner( new Person( 'host@example.com', 'Hanna Host' ) )
	->setImage( 'https://example.com/artwork-3000.jpg' )
	->addCategory( new Category( 'Technology' ) )
	->setExplicit( FALSE )
	->setType( 'episodic' ) );
```

See guide: [Podcasts](../../../guide/podcasts.md)
