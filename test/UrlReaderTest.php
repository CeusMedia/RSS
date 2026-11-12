<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Exception\ReadException;
use CeusMedia\RSS\Extension;
use CeusMedia\RSS\FeedParser;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Response;
use CeusMedia\RSS\UrlReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 *	Tests reading feeds by URL against a local PHP built-in web server.
 */
#[CoversClass( UrlReader::class )]
#[CoversClass( Response::class )]
#[CoversClass( Combiner::class )]
#[UsesClass( Dom::class )]
#[UsesClass( FeedParser::class )]
#[UsesClass( Parser::class )]
#[UsesClass( Extension\Extensions::class )]
#[UsesClass( Extension\AbstractExtension::class )]
#[UsesClass( Model\Category::class )]
#[UsesClass( Model\Channel::class )]
#[UsesClass( Model\Cloud::class )]
#[UsesClass( Model\Enclosure::class )]
#[UsesClass( Model\Guid::class )]
#[UsesClass( Model\Image::class )]
#[UsesClass( Model\Item::class )]
#[UsesClass( Model\Person::class )]
#[UsesClass( Model\Source::class )]
#[UsesClass( Model\TextInput::class )]
#[UsesClass( \CeusMedia\RSS\AtomParser::class )]
#[UsesClass( Extension\Atom\AtomExtension::class )]
#[UsesClass( Extension\Atom\ChannelData::class )]
#[UsesClass( Extension\Atom\Link::class )]
#[UsesClass( Extension\Content\ItemData::class )]
#[UsesClass( Extension\DublinCore\Data::class )]
#[UsesClass( Extension\ITunes\ITunesExtension::class )]
#[UsesClass( Extension\ITunes\ChannelData::class )]
#[UsesClass( Extension\ITunes\ItemData::class )]
#[RequiresPhpExtension( 'curl' )]
#[Group( 'server' )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
class UrlReaderTest extends TestCase
{
	/** @var list<resource> */
	private static array $processes	= [];

	private static string $baseUrl	= '';

	public static function setUpBeforeClass(): void
	{
		//  several workers, so slow requests of timeout tests do not block following tests
		self::$baseUrl	= self::startServer( 8 );
	}

	public static function tearDownAfterClass(): void
	{
		foreach( self::$processes as $process ){
			proc_terminate( $process );
			proc_close( $process );
		}
		self::$processes	= [];
	}

	public function testRead(): void
	{
		self::assertStringEqualsFile( __DIR__.'/fixtures/full.rss', UrlReader::read( self::$baseUrl.'/full.rss' ) );
	}

	public function testFetch(): void
	{
		$response	= ( new UrlReader() )->fetch( self::$baseUrl.'/full.rss' );
		self::assertSame( 200, $response->status );
		self::assertSame( self::$baseUrl.'/full.rss', $response->url );
		self::assertSame( 'application/rss+xml; charset=utf-8', $response->contentType );
		self::assertFalse( $response->isNotModified() );
	}

	public function testCombinerAddUrl(): void
	{
		$combiner	= ( new Combiner() )->addUrl( self::$baseUrl.'/full.rss' )->addUrl( self::$baseUrl.'/atom.xml' );
		self::assertCount( 5, $combiner->combine() );
	}

	public function testCombinerAddUrlsCollectsErrors(): void
	{
		$combiner	= new Combiner( new UrlReader( 5 ) );
		$errors		= $combiner->addUrls( [
			self::$baseUrl.'/full.rss',
			self::$baseUrl.'/missing.rss',
			self::$baseUrl.'/atom.xml',
			self::$baseUrl.'/big.rss',
		] );
		self::assertSame( [self::$baseUrl.'/missing.rss', self::$baseUrl.'/big.rss'], array_keys( $errors ) );
		self::assertInstanceOf( ReadException::class, $errors[self::$baseUrl.'/missing.rss'] );
		self::assertInstanceOf( \CeusMedia\RSS\Exception\ParseException::class, $errors[self::$baseUrl.'/big.rss'] );
		self::assertSame( $errors, $combiner->getErrors() );
		self::assertCount( 2, $combiner->getChannels() );
		$items	= $combiner->combine();
		self::assertCount( 5, $items );
		self::assertSame( self::$baseUrl.'/full.rss', ( $items[4] ?? NULL )?->getSource()?->url );
	}

	public function testCombinerAddUrlWithTimeout(): void
	{
		$this->expectException( ReadException::class );
		( new Combiner() )->addUrl( self::$baseUrl.'/sleep.rss?seconds=2', 1 );
	}

	public function testRedirectIsFollowed(): void
	{
		$response	= ( new UrlReader() )->fetch( self::$baseUrl.'/redirect.rss' );
		self::assertStringEqualsFile( __DIR__.'/fixtures/full.rss', $response->content );
		self::assertSame( self::$baseUrl.'/full.rss', $response->effectiveUrl );
	}

	public function testRedirectLoopIsStopped(): void
	{
		$this->expectException( ReadException::class );
		$this->expectExceptionMessage( 'redirect' );
		UrlReader::read( self::$baseUrl.'/redirect-loop.rss' );
	}

	public function testRedirectToFileIsBlocked(): void
	{
		$this->expectException( ReadException::class );
		UrlReader::read( self::$baseUrl.'/redirect-file.rss' );
	}

	public function testHeadersAreSent(): void
	{
		self::assertSame( UrlReader::USER_AGENT, Parser::parse( UrlReader::read( self::$baseUrl.'/user-agent.rss' ) )->getTitle() );
		self::assertSame( UrlReader::ACCEPT, Parser::parse( UrlReader::read( self::$baseUrl.'/accept.rss' ) )->getTitle() );
	}

	public function testCompressedResponseIsDecoded(): void
	{
		self::assertStringEqualsFile( __DIR__.'/fixtures/full.rss', UrlReader::read( self::$baseUrl.'/gzip.rss' ) );
	}

	public function testConditionalRequest(): void
	{
		$reader		= new UrlReader();
		$first		= $reader->fetch( self::$baseUrl.'/conditional.rss' );
		self::assertSame( 200, $first->status );
		self::assertSame( '"v1"', $first->etag );
		self::assertSame( 'Mon, 04 Mar 2024 10:00:00 GMT', $first->lastModified );

		$byEtag		= $reader->fetch( self::$baseUrl.'/conditional.rss', $first->etag );
		self::assertTrue( $byEtag->isNotModified() );
		self::assertSame( '', $byEtag->content );

		$byDate		= $reader->fetch( self::$baseUrl.'/conditional.rss', NULL, $first->lastModified );
		self::assertTrue( $byDate->isNotModified() );

		$changed	= $reader->fetch( self::$baseUrl.'/conditional.rss', '"v0"' );
		self::assertSame( 200, $changed->status );
	}

	/**
	 *	@return		array<string, array{string}>
	 */
	public static function bigResponseProvider(): array
	{
		return [
			'with Content-Length'		=> ['/big.rss'],
			'without Content-Length'	=> ['/big-chunked.rss'],
		];
	}

	#[DataProvider( 'bigResponseProvider' )]
	public function testResponseSizeIsLimited( string $path ): void
	{
		$this->expectException( ReadException::class );
		$this->expectExceptionMessage( 'response exceeds maximum size of 1048576 bytes' );
		( new UrlReader( maxSize: 1_048_576 ) )->fetch( self::$baseUrl.$path );
	}

	public function testHttpErrorsThrowException(): void
	{
		try{
			UrlReader::read( self::$baseUrl.'/missing.rss' );
			self::fail( 'Expected exception' );
		}
		catch( ReadException $e ){
			self::assertSame( 'Reading feed from '.self::$baseUrl.'/missing.rss failed: HTTP status 404', $e->getMessage() );
			self::assertSame( 404, $e->getCode() );
		}
	}

	public function testTimeoutThrowsException(): void
	{
		$start	= microtime( TRUE );
		try{
			UrlReader::read( self::$baseUrl.'/sleep.rss?seconds=2', 1 );
			self::fail( 'Expected timeout' );
		}
		catch( ReadException $e ){
			self::assertStringContainsString( 'timed out', $e->getMessage() );
			self::assertLessThan( 2.5, microtime( TRUE ) - $start );
		}
	}

	/**
	 *	@return		array<string, array{string}>
	 */
	public static function invalidUrlProvider(): array
	{
		return [
			'no URL'		=> ['not a url'],
			'file'			=> ['file:///etc/hostname'],
			'file host'		=> ['file://localhost/etc/hostname'],
			'gopher'		=> ['gopher://example.org/'],
			'no host'		=> ['http:///feed.rss'],
		];
	}

	#[DataProvider( 'invalidUrlProvider' )]
	public function testInvalidUrlsAreRejected( string $url ): void
	{
		$this->expectException( ReadException::class );
		$this->expectExceptionMessage( 'only HTTP and HTTPS URLs are allowed' );
		UrlReader::read( $url );
	}

	public function testFetchAllRunsInParallel(): void
	{
		//  one server per request: a worker of the PHP web server may accept several connections, but handles them one by one
		$urls	= [];
		foreach( ['a', 'b', 'c'] as $key )
			$urls[$key]	= self::startServer( 1 ).'/sleep.rss?seconds=1';
		$start		= microtime( TRUE );
		$results	= ( new UrlReader() )->fetchAll( $urls );
		self::assertLessThan( 2.5, microtime( TRUE ) - $start );
		self::assertSame( ['a', 'b', 'c'], array_keys( $results ) );
		foreach( $results as $result )
			self::assertInstanceOf( Response::class, $result );
	}

	public function testFetchAllKeepsKeysAndReportsFailuresSeparately(): void
	{
		$results	= ( new UrlReader( maxParallel: 2 ) )->fetchAll( [
			'missing'	=> self::$baseUrl.'/missing.rss',
			'ok'		=> self::$baseUrl.'/full.rss',
			'invalid'	=> 'ftp://example.org/feed.rss',
			'error'		=> self::$baseUrl.'/error.rss',
			'cond'		=> ['url' => self::$baseUrl.'/conditional.rss', 'etag' => '"v1"'],
		] );
		self::assertSame( ['missing', 'ok', 'invalid', 'error', 'cond'], array_keys( $results ) );
		self::assertInstanceOf( ReadException::class, $results['missing'] );
		self::assertInstanceOf( Response::class, $results['ok'] );
		self::assertInstanceOf( ReadException::class, $results['invalid'] );
		self::assertInstanceOf( ReadException::class, $results['error'] );
		self::assertSame( 500, $results['error']->getCode() );
		self::assertInstanceOf( Response::class, $results['cond'] );
		self::assertTrue( $results['cond']->isNotModified() );
	}

	public function testConnectionErrorThrowsException(): void
	{
		$this->expectException( ReadException::class );
		$this->expectExceptionMessage( 'Reading feed from http://127.0.0.1:1/feed.rss failed: ' );
		UrlReader::read( 'http://127.0.0.1:1/feed.rss' );
	}

	/**
	 *	Starts PHP web server with test router and returns its base URL.
	 */
	private static function startServer( int $workers ): string
	{
		$port		= self::findFreePort();
		$command	= [PHP_BINARY, '-S', '127.0.0.1:'.$port, __DIR__.'/fixtures/server/router.php'];
		$environment	= ( $workers > 1 ? ['PHP_CLI_SERVER_WORKERS' => (string) $workers] : [] ) + getenv();
		$process	= proc_open( $command, [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes, NULL, $environment );
		if( FALSE === $process )
			throw new RuntimeException( 'Starting PHP web server failed' );
		self::$processes[]	= $process;
		self::waitForPort( $port );
		return 'http://127.0.0.1:'.$port;
	}

	private static function findFreePort(): int
	{
		$socket	= stream_socket_server( 'tcp://127.0.0.1:0' );
		if( FALSE === $socket )
			throw new RuntimeException( 'No free port found' );
		$name	= (string) stream_socket_get_name( $socket, FALSE );
		fclose( $socket );
		return (int) substr( $name, (int) strrpos( $name, ':' ) + 1 );
	}

	private static function waitForPort( int $port ): void
	{
		for( $i = 0; $i < 50; $i++ ){
			$connection	= @fsockopen( '127.0.0.1', $port, $errno, $error, 0.1 );
			if( FALSE !== $connection ){
				fclose( $connection );
				return;
			}
			usleep( 50000 );
		}
		throw new RuntimeException( 'PHP web server did not start' );
	}
}
