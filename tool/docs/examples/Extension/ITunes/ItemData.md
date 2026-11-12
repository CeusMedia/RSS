```php
use CeusMedia\RSS\Extension\ITunes\ItemData as EpisodeData;

$item->setExtension( ( new EpisodeData() )
	->setTitle( 'What is RSS?' )
	->setEpisode( 1 )
	->setSeason( 1 )
	->setDuration( 1810 )
	->setEpisodeType( 'full' ) );
```

See guide: [Episodes](../../../guide/podcasts.md#episodes)
