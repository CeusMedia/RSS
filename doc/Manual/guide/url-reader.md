# Reading from URLs

[`UrlReader`](../api/UrlReader.md) reads feeds from URLs using cURL with secure settings.

```php
use CeusMedia\RSS\UrlReader;

$xml	= UrlReader::read( 'https://example.org/feed.rss' );
```

## Defaults

| Setting | Default | Change by |
|---------|---------|-----------|
| Protocols | HTTP and HTTPS only, also for redirects | – |
| TLS | certificate and host name are verified | – |
| Timeout | 10 seconds, connect 5 seconds | `new UrlReader( timeout: 5 )` |
| Size limit | 10 MiB | `new UrlReader( maxSize: 2_097_152 )` |
| Parallel requests | 10 | `new UrlReader( maxParallel: 4 )` |
| Redirects | at most 5 | – |
| Compression | gzip, deflate (and brotli, if supported by cURL) | – |
| User agent | `CeusMedia-RSS/0.5 (+https://github.com/CeusMedia/RSS)` | – |

Failures throw a [`ReadException`](errors.md), having the HTTP status or cURL error number as code.

## Conditional requests

Feeds are read again and again. With the ETag and Last-Modified of the previous response, the server answers
`304 Not Modified` if nothing changed – without sending the feed:

```php
$reader		= new UrlReader();
$response	= $reader->fetch( $url, $state['etag'] ?? NULL, $state['lastModified'] ?? NULL );

if( !$response->isNotModified() ){
	$channel	= FeedParser::parse( $response->content );
	//  … process new items …
	$state	= ['etag' => $response->etag, 'lastModified' => $response->lastModified];
}
```

[`Response`](../api/Response.md) also contains the status, the content type and the URL after redirects.

## Parallel requests

`fetchAll()` reads several feeds at once. Failures do not stop other requests, the result contains a
`Response` or a `ReadException` per key:

```php
use CeusMedia\RSS\Exception\ReadException;

$results	= ( new UrlReader() )->fetchAll( [
	'news'		=> 'https://example.org/feed.rss',
	'blog'		=> ['url' => 'https://example.com/feed.atom', 'etag' => $etag],
] );
foreach( $results as $key => $result ){
	if( $result instanceof ReadException ){
		error_log( $key.': '.$result->getMessage() );
		continue;
	}
	$channel	= FeedParser::parse( $result->content );
}
```

[`Combiner::addUrls()`](combining.md#reading-feeds) uses it to read feeds for combining.
