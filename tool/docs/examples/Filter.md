```php
use CeusMedia\RSS\Filter;

$combiner
	->addFilter( Filter::category( 'Releases', 'Security' ) )
	->addFilter( Filter::since( new DateTimeImmutable( '-30 days' ) ) )
	->addFilter( Filter::not( Filter::search( 'sponsored' ) ) );
```

See guide: [Filters](../guide/combining.md#filters)
