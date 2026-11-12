<?php
declare( strict_types=1 );

/**
 *	Reads feeds from URLs with secure defaults, conditional requests and parallel downloads.
 *
 *	Copyright (c) 2012-2026 Christian Würker / {@link https://ceusmedia.de/ Ceus Media}
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
namespace CeusMedia\RSS;

use CeusMedia\RSS\Exception\ReadException;
use CurlHandle;

/**
 *	Reads feeds from URLs using cURL with secure defaults:
 *	- only HTTP and HTTPS, also for redirects
 *	- TLS certificate and host name are verified
 *	- timeout and maximum response size
 *	- at most 5 redirects
 *	- compressed transfer (gzip, deflate, brotli if supported)
 *	- conditional requests (ETag, Last-Modified) to avoid downloading unchanged feeds
 *	- several feeds can be read in parallel
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class UrlReader
{
	/**	Default maximum duration of a request, in seconds */
	public const int TIMEOUT			= 10;

	/**	Maximum duration of connecting to a server, in seconds */
	public const int CONNECT_TIMEOUT	= 5;

	/**	Maximum number of redirects to follow */
	public const int MAX_REDIRECTS		= 5;

	/**	Default maximum size of a response, in bytes (10 MiB) */
	public const int MAX_SIZE			= 10_485_760;

	/**	Default maximum number of parallel requests */
	public const int MAX_PARALLEL		= 10;

	/**	Value of User-Agent header */
	public const string USER_AGENT		= 'CeusMedia-RSS/0.5 (+https://github.com/CeusMedia/RSS)';

	/**	Value of Accept header, preferring feed formats */
	public const string ACCEPT			= 'application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.8, */*;q=0.5';

	/**
	 *	Creates reader with limits for time, size and parallel requests.
	 *	@param		int		$timeout		Maximum duration of a request in seconds
	 *	@param		int		$maxSize		Maximum size of a response in bytes
	 *	@param		int		$maxParallel	Maximum number of parallel requests in readAll()
	 */
	public function __construct(
		protected int $timeout = self::TIMEOUT,
		protected int $maxSize = self::MAX_SIZE,
		protected int $maxParallel = self::MAX_PARALLEL,
	)
	{
	}

	/**
	 *	Reads content from URL.
	 *	@param		string		$url		URL to read
	 *	@param		int			$timeout	Maximum duration of request in seconds
	 *	@return		string
	 *	@throws		ReadException			if URL is invalid, request failed, timed out or response is too large
	 */
	public static function read( string $url, int $timeout = self::TIMEOUT ): string
	{
		return ( new self( $timeout ) )->fetch( $url )->content;
	}

	/**
	 *	Reads feed from URL, using a conditional request if ETag or Last-Modified of a previous response is given.
	 *	If the feed has not changed, the response has status 304 and empty content, see Response::isNotModified().
	 *	@param		string		$url			URL to read
	 *	@param		?string		$etag			ETag of previous response
	 *	@param		?string		$lastModified	Last-Modified of previous response
	 *	@return		Response
	 *	@throws		ReadException				if URL is invalid, request failed, timed out or response is too large
	 */
	public function fetch( string $url, ?string $etag = NULL, ?string $lastModified = NULL ): Response
	{
		$result	= $this->fetchAll( [$url => ['url' => $url, 'etag' => $etag, 'lastModified' => $lastModified]] )[$url];
		if( $result instanceof ReadException )
			throw $result;
		return $result;
	}

	/**
	 *	Reads several feeds in parallel. Failures do not stop other requests.
	 *	@template	K of array-key
	 *	@param		array<K, string|array{url: string, etag?: ?string, lastModified?: ?string}>	$requests	Map of URLs or requests with ETag and Last-Modified
	 *	@return		array<K, Response|ReadException>	Responses or exceptions, with keys of given requests
	 */
	public function fetchAll( array $requests ): array
	{
		$results	= [];
		$queue		= [];
		foreach( $requests as $key => $request ){
			$request	= is_string( $request ) ? ['url' => $request] : $request;
			$error		= $this->validateUrl( $request['url'] );
			if( NULL !== $error )
				$results[$key]	= $error;
			else
				$queue[]	= [$key, $request['url'], $request['etag'] ?? NULL, $request['lastModified'] ?? NULL];
		}
		foreach( array_chunk( $queue, max( 1, $this->maxParallel ) ) as $chunk )
			foreach( $this->executeParallel( $chunk ) as $key => $result )
				$results[$key]	= $result;
		$sorted	= [];
		foreach( array_keys( $requests ) as $key )
			$sorted[$key]	= $results[$key];
		return $sorted;
	}

	/**
	 *	Creates cURL handle with secure defaults.
	 *	@param		string					$url		URL
	 *	@param		?string					$etag		ETag of previous response
	 *	@param		?string					$lastModified		Last-Modified of previous response
	 *	@param		array<string, string>	$headers		Collector of response headers, filled by reference
	 *	@return		CurlHandle
	 *	@throws		ReadException			if cURL cannot be initialized
	 */
	protected function createHandle( string $url, ?string $etag, ?string $lastModified, array &$headers ): CurlHandle
	{
		$handle	= curl_init();
		if( FALSE === $handle )
			throw new ReadException( 'Initializing cURL failed' );
		$requestHeaders	= ['Accept: '.self::ACCEPT];
		if( NULL !== $etag && '' !== $etag )
			$requestHeaders[]	= 'If-None-Match: '.$etag;
		if( NULL !== $lastModified && '' !== $lastModified )
			$requestHeaders[]	= 'If-Modified-Since: '.$lastModified;
		$maxSize	= $this->maxSize;
		curl_setopt_array( $handle, [
			CURLOPT_URL					=> $url,
			CURLOPT_RETURNTRANSFER		=> TRUE,
			CURLOPT_FOLLOWLOCATION		=> TRUE,
			CURLOPT_MAXREDIRS			=> self::MAX_REDIRECTS,
			CURLOPT_PROTOCOLS			=> CURLPROTO_HTTP | CURLPROTO_HTTPS,
			CURLOPT_REDIR_PROTOCOLS		=> CURLPROTO_HTTP | CURLPROTO_HTTPS,
			CURLOPT_SSL_VERIFYPEER		=> TRUE,
			CURLOPT_SSL_VERIFYHOST		=> 2,
			CURLOPT_TIMEOUT				=> $this->timeout,
			CURLOPT_CONNECTTIMEOUT		=> min( $this->timeout, self::CONNECT_TIMEOUT ),
			CURLOPT_USERAGENT			=> self::USER_AGENT,
			CURLOPT_ENCODING			=> '',
			CURLOPT_HTTPHEADER			=> $requestHeaders,
			CURLOPT_MAXFILESIZE			=> $maxSize,
			CURLOPT_NOPROGRESS			=> FALSE,
			CURLOPT_XFERINFOFUNCTION	=> static fn( CurlHandle $handle, int $downloadTotal, int $downloaded ): int => $downloaded > $maxSize ? 1 : 0,
			CURLOPT_HEADERFUNCTION		=> static function( CurlHandle $handle, string $line ) use ( &$headers ): int {
				if( str_starts_with( $line, 'HTTP/' ) )
					$headers	= [];
				$parts	= explode( ':', $line, 2 );
				if( 2 === count( $parts ) )
					$headers[strtolower( trim( $parts[0] ) )]	= trim( $parts[1] );
				return strlen( $line );
			},
		] );
		return $handle;
	}

	/**
	 *	Executes requests in parallel.
	 *	@template	K of array-key
	 *	@param		list<array{0: K, 1: string, 2: ?string, 3: ?string}>	$requests
	 *	@return		array<K, Response|ReadException>
	 */
	protected function executeParallel( array $requests ): array
	{
		$multi		= curl_multi_init();
		$handles	= [];
		$headers	= [];
		$results	= [];
		foreach( $requests as $index => [$key, $url, $etag, $lastModified] ){
			$headers[$index]	= [];
			try{
				$handle	= $this->createHandle( $url, $etag, $lastModified, $headers[$index] );
			}
			catch( ReadException $e ){
				$results[$key]	= $e;
				continue;
			}
			$handles[$index]	= $handle;
			curl_multi_add_handle( $multi, $handle );
		}
		$codes	= [];
		do{
			$status	= curl_multi_exec( $multi, $running );
			while( FALSE !== ( $info = curl_multi_info_read( $multi ) ) )
				if( $info['handle'] instanceof CurlHandle && is_int( $info['result'] ) )
					$codes[spl_object_id( $info['handle'] )]	= $info['result'];
			if( $running > 0 )
				curl_multi_select( $multi, 0.1 );
		}
		while( $running > 0 && CURLM_OK === $status );

		foreach( $handles as $index => $handle ){
			[$key, $url]	= [$requests[$index][0], $requests[$index][1]];
			$code			= $codes[spl_object_id( $handle )] ?? CURLE_OK;
			$results[$key]	= $this->createResult( $url, $handle, $code, $headers[$index] );
			curl_multi_remove_handle( $multi, $handle );
			curl_close( $handle );
		}
		curl_multi_close( $multi );
		return $results;
	}

	/**
	 *	Creates response or exception from finished cURL transfer.
	 *	@param		string					$url		URL
	 *	@param		CurlHandle				$handle		cURL handle
	 *	@param		int						$code		cURL result code
	 *	@param		array<string, string>	$headers	Response headers, lower case names
	 *	@return		Response|ReadException
	 */
	protected function createResult( string $url, CurlHandle $handle, int $code, array $headers ): Response|ReadException
	{
		$prefix	= 'Reading feed from '.$url.' failed: ';
		if( CURLE_ABORTED_BY_CALLBACK === $code || CURLE_FILESIZE_EXCEEDED === $code )
			return new ReadException( $prefix.'response exceeds maximum size of '.$this->maxSize.' bytes', $code );
		if( CURLE_OK !== $code ){
			$error	= curl_error( $handle );
			return new ReadException( $prefix.( '' !== $error ? $error : (string) curl_strerror( $code ) ), $code );
		}
		$status	= curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
		if( 304 !== $status && ( $status < 200 || $status > 299 ) )
			return new ReadException( $prefix.'HTTP status '.$status, $status );
		$content	= curl_multi_getcontent( $handle ) ?? '';
		return new Response(
			$url,
			$status,
			304 === $status ? '' : $content,
			$headers['etag'] ?? NULL,
			$headers['last-modified'] ?? NULL,
			$headers['content-type'] ?? NULL,
			curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL ),
		);
	}

	/**
	 *	Checks that URL uses HTTP or HTTPS and has a host, returns exception otherwise.
	 *	@param		string		$url		URL
	 *	@return		?ReadException
	 */
	protected function validateUrl( string $url ): ?ReadException
	{
		$scheme	= strtolower( (string) parse_url( $url, PHP_URL_SCHEME ) );
		$host	= (string) parse_url( $url, PHP_URL_HOST );
		if( !in_array( $scheme, ['http', 'https'], TRUE ) || '' === $host )
			return new ReadException( 'Reading feed from '.$url.' failed: only HTTP and HTTPS URLs are allowed' );
		return NULL;
	}
}
