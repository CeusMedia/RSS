<?php
declare( strict_types=1 );

/**
 *	Router for PHP built-in web server used by UrlReaderTest.
 */

function serverValue( string $key ): string
{
	$value	= $_SERVER[$key] ?? '';
	return is_string( $value ) ? $value : '';
}

function sendFeed( string $title ): void
{
	header( 'Content-Type: application/rss+xml; charset=utf-8' );
	print '<rss version="2.0"><channel><title>'.htmlspecialchars( $title, ENT_XML1 ).'</title><link>l</link><description>d</description></channel></rss>';
}

$path	= (string) parse_url( serverValue( 'REQUEST_URI' ), PHP_URL_PATH );
$query	= [];
parse_str( (string) parse_url( serverValue( 'REQUEST_URI' ), PHP_URL_QUERY ), $query );
$full	= dirname( __DIR__ ).'/full.rss';

switch( $path ){
	case '/full.rss':
		header( 'Content-Type: application/rss+xml; charset=utf-8' );
		readfile( $full );
		break;
	case '/atom.xml':
		header( 'Content-Type: application/atom+xml; charset=utf-8' );
		readfile( dirname( __DIR__ ).'/atom.xml' );
		break;
	case '/user-agent.rss':
		sendFeed( serverValue( 'HTTP_USER_AGENT' ) );
		break;
	case '/accept.rss':
		sendFeed( serverValue( 'HTTP_ACCEPT' ) );
		break;
	case '/redirect.rss':
		header( 'Location: /full.rss', TRUE, 301 );
		break;
	case '/redirect-loop.rss':
		header( 'Location: /redirect-loop.rss', TRUE, 302 );
		break;
	case '/redirect-file.rss':
		header( 'Location: file:///etc/hostname', TRUE, 302 );
		break;
	case '/sleep.rss':
		$seconds	= $query['seconds'] ?? '1';
		sleep( is_string( $seconds ) ? (int) $seconds : 1 );
		sendFeed( 'slept' );
		break;
	case '/big.rss':
		header( 'Content-Length: 2097152' );
		print str_repeat( 'x', 2_097_152 );
		break;
	case '/big-chunked.rss':
		for( $i = 0; $i < 32; $i++ ){
			print str_repeat( 'x', 65536 );
			flush();
		}
		break;
	case '/conditional.rss':
		$etag			= '"v1"';
		$lastModified	= 'Mon, 04 Mar 2024 10:00:00 GMT';
		header( 'ETag: '.$etag );
		header( 'Last-Modified: '.$lastModified );
		if( $etag === serverValue( 'HTTP_IF_NONE_MATCH' ) || $lastModified === serverValue( 'HTTP_IF_MODIFIED_SINCE' ) ){
			http_response_code( 304 );
			break;
		}
		sendFeed( 'conditional' );
		break;
	case '/gzip.rss':
		if( !str_contains( serverValue( 'HTTP_ACCEPT_ENCODING' ), 'gzip' ) ){
			http_response_code( 406 );
			break;
		}
		header( 'Content-Type: application/rss+xml; charset=utf-8' );
		header( 'Content-Encoding: gzip' );
		print gzencode( (string) file_get_contents( $full ) );
		break;
	case '/error.rss':
		http_response_code( 500 );
		break;
	default:
		http_response_code( 404 );
		print 'Not found';
}
