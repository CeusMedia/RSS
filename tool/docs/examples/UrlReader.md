```php
use CeusMedia\RSS\UrlReader;

$xml		= UrlReader::read( 'https://example.org/feed.rss' );

$reader		= new UrlReader( timeout: 5, maxSize: 2_097_152 );
$response	= $reader->fetch( $url, $previousEtag, $previousLastModified );
if( !$response->isNotModified() )
	$channel	= FeedParser::parse( $response->content );

$results	= $reader->fetchAll( ['news' => $url1, 'blog' => $url2] );   // Response or ReadException per key
```

See guide: [Reading from URLs](../guide/url-reader.md)
