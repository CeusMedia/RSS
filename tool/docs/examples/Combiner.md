```php
use CeusMedia\RSS\{Combiner, Filter};
use CeusMedia\RSS\Model\Channel;

$combiner	= new Combiner();
$errors		= $combiner->addUrls( ['https://example.org/feed.rss', 'https://example.com/feed.atom'] );
$combiner->addFilter( Filter::since( new DateTimeImmutable( '-30 days' ) ) );

$items		= $combiner->combine( 10 );
$xml		= $combiner->render( new Channel( 'Planet', 'https://planet.example.org/', 'All feeds' ), 10 );
```

See guide: [Combining feeds](../guide/combining.md)
