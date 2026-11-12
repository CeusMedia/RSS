<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Exception\ParseException;
use DOMDocument;
use DOMException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Dom::class )]
class DomTest extends TestCase
{
	public function testAppend(): void
	{
		$document	= new DOMDocument();
		$root		= Dom::append( $document, 'root' );
		Dom::append( $root, 'a', 'x & y', ['b' => '1', 'c' => NULL] );
		Dom::append( $root, 'p:n', 'v', [], 'urn:p' );
		self::assertSame( '<root><a b="1">x &amp; y</a><p:n xmlns:p="urn:p">v</p:n></root>', $document->saveXML( $document->documentElement ) );
	}

	public function testAppendToDetachedElementThrowsException(): void
	{
		$this->expectException( DOMException::class );
		Dom::append( new \DOMElement( 'detached' ), 'child' );
	}

	public function testLoad(): void
	{
		$root	= Dom::load( '<?xml version="1.0"?><!-- comment --><root><a/></root>' );
		self::assertSame( 'root', $root->nodeName );
	}

	public function testLoadEmptyStringThrowsException(): void
	{
		$this->expectException( ParseException::class );
		$this->expectExceptionMessage( 'Invalid XML: empty string given' );
		Dom::load( " \n" );
	}

	public function testLoadInvalidXmlThrowsExceptionWithAllErrors(): void
	{
		try{
			Dom::load( '<a><b></a>' );
			self::fail( 'Expected exception' );
		}
		catch( ParseException $e ){
			self::assertStringStartsWith( 'Invalid XML: ', $e->getMessage() );
			self::assertStringContainsString( '; ', $e->getMessage() );
			self::assertStringNotContainsString( PHP_EOL, $e->getMessage() );
		}
	}

	public function testGetChildren(): void
	{
		$document	= new DOMDocument();
		$document->loadXML( '<root xmlns:p="urn:p"><a/>text<p:b/><!-- c --><d/></root>' );
		$root		= $document->documentElement;
		self::assertNotNull( $root );
		self::assertSame( ['a', 'd'], array_map( static fn( \DOMElement $e ): string => $e->nodeName, Dom::getChildren( $root ) ) );
		self::assertSame( ['p:b'], array_map( static fn( \DOMElement $e ): string => $e->nodeName, Dom::getChildren( $root, 'urn:p' ) ) );
		self::assertCount( 3, Dom::getChildren( $root, FALSE ) );
	}

	public function testTextHelpers(): void
	{
		self::assertTrue( Dom::isFilled( 'x' ) );
		self::assertFalse( Dom::isFilled( ' ' ) );
		self::assertFalse( Dom::isFilled( NULL ) );
		self::assertSame( 'x', Dom::nullIfEmpty( ' x ' ) );
		self::assertNull( Dom::nullIfEmpty( ' ' ) );
		self::assertNull( Dom::nullIfEmpty( NULL ) );
	}

	public function testSanitize(): void
	{
		self::assertSame( 'ab', Dom::sanitize( "a\x00\x0Bb" ) );
		self::assertSame( "a\tb\nc\rd", Dom::sanitize( "a\tb\nc\rd" ) );
		self::assertSame( "x \u{FFFD}", Dom::sanitize( "x \xFF" ) );
		self::assertSame( 'Größe 😀', Dom::sanitize( 'Größe 😀' ) );
	}
}
