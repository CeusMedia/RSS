<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Parser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass( Parser::class )]
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
#[UsesClass( \CeusMedia\RSS\Dom::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\AtomExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Extensions::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ITunesExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\ChannelData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\Link::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ItemData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\Data::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\Category::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ItemData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ChannelData::class )]
class ParserTest extends TestCase
{
	public function testParseFullFeed(): void
	{
		$channel	= Parser::parse( self::readFixture( 'full.rss' ) );

		self::assertSame( 'Example Channel', $channel->getTitle() );
		self::assertSame( 'https://example.org/', $channel->getLink() );
		self::assertSame( 'News & updates from example.org', $channel->getDescription() );
		self::assertSame( 'en-us', $channel->getLanguage() );
		self::assertSame( 'Copyright 2024 Example Inc.', $channel->getCopyright() );
		self::assertEquals( new Model\Person( 'editor@example.org', 'Erika Editor' ), $channel->getManagingEditor() );
		self::assertEquals( new Model\Person( 'webmaster@example.org', 'Willi Webmaster' ), $channel->getWebMaster() );
		self::assertSame( '2024-01-01T12:00:00+00:00', $channel->getPubDate()?->format( DATE_ATOM ) );
		self::assertSame( '2024-01-02T08:30:00+01:00', $channel->getLastBuildDate()?->format( DATE_ATOM ) );
		self::assertEquals( [
			new Model\Category( 'Technology' ),
			new Model\Category( 'Web/Feeds', 'https://example.org/taxonomy' ),
		], $channel->getCategories() );
		self::assertSame( 'CeusMedia/RSS', $channel->getGenerator() );
		self::assertSame( 'https://www.rssboard.org/rss-specification', $channel->getDocs() );
		self::assertEquals( new Model\Cloud( 'rpc.example.org', 80, '/RPC2', 'pingMe', 'soap' ), $channel->getCloud() );
		self::assertSame( 60, $channel->getTtl() );
		self::assertStringStartsWith( '(PICS-1.1', (string) $channel->getRating() );
		self::assertEquals( new Model\TextInput( 'Search', 'Search the archive', 'q', 'https://example.org/search' ), $channel->getTextInput() );
		self::assertSame( [0, 1, 23], $channel->getSkipHours() );
		self::assertSame( ['Saturday', 'Sunday'], $channel->getSkipDays() );

		$image	= $channel->getImage();
		self::assertNotNull( $image );
		self::assertSame( 'https://example.org/logo.png', $image->getUrl() );
		self::assertSame( 'Example Logo', $image->getTitle() );
		self::assertSame( 'https://example.org/', $image->getLink() );
		self::assertSame( 'Logo of example.org', $image->getDescription() );
		self::assertSame( 88, $image->getWidth() );
		self::assertSame( 31, $image->getHeight() );

		$items	= $channel->getItems();
		self::assertCount( 3, $items );
		self::assertSame( 'First item', $items[0]->getTitle() );
		self::assertSame( 'https://example.org/news/1', $items[0]->getLink() );
		self::assertSame( '<p>Hello & welcome</p>', $items[0]->getDescription() );
		self::assertEquals( new Model\Person( 'author@example.org', 'Anna Author' ), $items[0]->getAuthor() );
		self::assertEquals( [
			new Model\Category( 'News' ),
			new Model\Category( 'welcome', 'https://example.org/tags' ),
		], $items[0]->getCategories() );
		self::assertSame( 'https://example.org/news/1#comments', $items[0]->getComments() );
		self::assertEquals( new Model\Enclosure( 'https://example.org/media/episode1.mp3', 12345678, 'audio/mpeg' ), $items[0]->getEnclosure() );
		self::assertEquals( new Model\Guid( 'https://example.org/news/1', TRUE ), $items[0]->getGuid() );
		self::assertSame( '2024-01-01T10:00:00+00:00', $items[0]->getPubDate()?->format( DATE_ATOM ) );
		self::assertEquals( new Model\Source( 'Other feed', 'https://other.example.org/rss?a=1&b=2' ), $items[0]->getSource() );
		self::assertEquals( new Model\Guid( 'urn:uuid:2c5e4f8a-0000-4000-8000-000000000002', FALSE ), $items[1]->getGuid() );
		self::assertNull( $items[2]->getTitle() );
		self::assertSame( 'Item without title', $items[2]->getDescription() );
	}

	public function testExtensionElementsAreIgnored(): void
	{
		$channel	= Parser::parse( self::readFixture( 'extensions.rss' ) );
		self::assertSame( 'https://example.org/', $channel->getLink() );
		$item	= $channel->getItems()[0];
		self::assertSame( 'Real title', $item->getTitle() );
		self::assertSame( 'Short', $item->getDescription() );
		self::assertNull( $item->getAuthor() );
	}

	public function testValuesAreTrimmed(): void
	{
		$item	= self::parseItem( "<title>\n   Title  \n</title><description>  Content  </description>" );
		self::assertSame( 'Title', $item->getTitle() );
		self::assertSame( 'Content', $item->getDescription() );
	}

	/**
	 *	@return		array<string, array{string, bool}>
	 */
	public static function guidProvider(): array
	{
		return [
			'attribute missing'	=> ['<guid>https://example.org/1</guid>', TRUE],
			'attribute true'	=> ['<guid isPermaLink="true">https://example.org/1</guid>', TRUE],
			'attribute false'	=> ['<guid isPermaLink="false">https://example.org/1</guid>', FALSE],
			'attribute FALSE'	=> ['<guid isPermaLink="FALSE">https://example.org/1</guid>', FALSE],
		];
	}

	#[DataProvider( 'guidProvider' )]
	public function testGuidPermaLinkDefaultsToTrue( string $guid, bool $expected ): void
	{
		$item	= self::parseItem( '<title>x</title>'.$guid );
		self::assertEquals( new Model\Guid( 'https://example.org/1', $expected ), $item->getGuid() );
	}

	public function testCdataDescription(): void
	{
		$item	= self::parseItem( '<title>x</title><description><![CDATA[<p>Hi &amp; bye</p>]]></description>' );
		self::assertSame( '<p>Hi &amp; bye</p>', $item->getDescription() );
	}

	/**
	 *	@return		array<string, array{string, ?string}>
	 */
	public static function dateProvider(): array
	{
		return [
			'RFC 822'				=> ['Thu, 07 Mar 2024 09:05:00 +0000', '2024-03-07T09:05:00+00:00'],
			'RFC 822 with offset'	=> ['Thu, 07 Mar 2024 09:05:00 -0500', '2024-03-07T09:05:00-05:00'],
			'GMT'					=> ['Thu, 07 Mar 2024 09:05:00 GMT', '2024-03-07T09:05:00+00:00'],
			'without weekday'		=> ['07 Mar 2024 09:05:00 +0000', '2024-03-07T09:05:00+00:00'],
			'ISO 8601'				=> ['2024-03-07T09:05:00+02:00', '2024-03-07T09:05:00+02:00'],
			'invalid'				=> ['not a date', NULL],
			'empty'					=> ['', NULL],
		];
	}

	#[DataProvider( 'dateProvider' )]
	public function testDates( string $value, ?string $expected ): void
	{
		$item	= self::parseItem( '<title>x</title><pubDate>'.$value.'</pubDate>' );
		self::assertSame( $expected, $item->getPubDate()?->format( DATE_ATOM ) );
	}

	public function testInvalidOptionalValuesAreSkipped(): void
	{
		$channel	= Parser::parse( self::wrapChannel(
			'<ttl>soon</ttl>'.
			'<cloud domain="d" port="none" path="/" registerProcedure="p" protocol="soap"/>'.
			'<image><url>u</url><width>abc</width><height>999</height></image>'.
			'<textInput><title>t</title></textInput>'.
			'<skipHours><hour>24</hour><hour>x</hour><hour>3</hour></skipHours>'.
			'<skipDays><day>Caturday</day><day>friday</day></skipDays>'.
			'<category> </category>'.
			'<item><title>x</title><enclosure url="" length="1" type="a/b"/><source>No URL</source><guid> </guid><author></author></item>'
		) );
		self::assertNull( $channel->getTtl() );
		self::assertNull( $channel->getCloud() );
		self::assertNull( $channel->getTextInput() );
		self::assertSame( [3], $channel->getSkipHours() );
		self::assertSame( ['Friday'], $channel->getSkipDays() );
		self::assertSame( [], $channel->getCategories() );
		self::assertSame( 'u', $channel->getImage()?->getUrl() );
		self::assertNull( $channel->getImage()->getWidth() );
		self::assertNull( $channel->getImage()->getHeight() );
		$item	= $channel->getItems()[0];
		self::assertNull( $item->getEnclosure() );
		self::assertNull( $item->getSource() );
		self::assertNull( $item->getGuid() );
		self::assertNull( $item->getAuthor() );
	}

	public function testImageWithoutUrlIsSkipped(): void
	{
		self::assertNull( Parser::parse( self::wrapChannel( '<image><title>t</title></image>' ) )->getImage() );
	}

	public function testEnclosureWithMissingLengthAndType(): void
	{
		$item	= self::parseItem( '<title>x</title><enclosure url="https://example.org/a.mp3"/>' );
		self::assertEquals( new Model\Enclosure( 'https://example.org/a.mp3', 0, 'application/octet-stream' ), $item->getEnclosure() );
	}

	public function testDocsAreOnlySetIfPresent(): void
	{
		$channel	= Parser::parse( self::wrapChannel( '' ) );
		self::assertNull( $channel->getDocs() );
		$channel	= Parser::parse( self::wrapChannel( '<docs>https://example.org/docs</docs>' ) );
		self::assertSame( 'https://example.org/docs', $channel->getDocs() );
	}

	/**
	 *	@return		array<string, array{string, string}>
	 */
	public static function invalidInputProvider(): array
	{
		return [
			'empty string'			=> ['', 'empty string'],
			'whitespace only'		=> ["  \n ", 'empty string'],
			'broken XML'			=> ['<rss><channel>', 'Invalid XML'],
			'not XML'				=> ['Hello World', 'Invalid XML'],
			'Atom feed'				=> ['<feed xmlns="http://www.w3.org/2005/Atom"><title>x</title></feed>', 'root element is <feed>'],
			'RSS 1.0 (RDF)'			=> ['<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"/>', 'root element is <rdf:RDF>'],
			'namespaced rss'		=> ['<x:rss xmlns:x="urn:x"><x:channel/></x:rss>', 'root element is <x:rss>'],
			'missing channel'		=> ['<rss version="2.0"/>', 'missing <channel>'],
			'namespaced channel'	=> ['<rss version="2.0" xmlns:x="urn:x"><x:channel/></rss>', 'missing <channel>'],
		];
	}

	#[DataProvider( 'invalidInputProvider' )]
	public function testInvalidInputThrowsParseException( string $xml, string $message ): void
	{
		$this->expectException( ParseException::class );
		$this->expectExceptionMessage( $message );
		Parser::parse( $xml );
	}

	public function testParseExceptionIsRuntimeException(): void
	{
		$this->expectException( RuntimeException::class );
		Parser::parse( '<rss>' );
	}

	public function testInvalidInputDoesNotLeakLibxmlErrors(): void
	{
		try{
			Parser::parse( '<rss><channel>' );
		}
		catch( ParseException ){
		}
		self::assertSame( [], libxml_get_errors() );
		self::assertFalse( libxml_use_internal_errors() );
	}

	public function testExternalEntitiesAreNotResolved(): void
	{
		$file	= tempnam( sys_get_temp_dir(), 'rss' );
		self::assertIsString( $file );
		file_put_contents( $file, 'SECRET' );
		$xml	= '<?xml version="1.0"?><!DOCTYPE rss [<!ENTITY xxe SYSTEM "file://'.$file.'">]>'.
			'<rss version="2.0"><channel><title>&xxe;</title><link>l</link><description>d</description></channel></rss>';
		try{
			$channel	= Parser::parse( $xml );
			self::assertStringNotContainsString( 'SECRET', $channel->getTitle() );
		}
		catch( ParseException ){
			$this->addToAssertionCount( 1 );
		}
		finally{
			unlink( $file );
		}
	}

	public function testExtensionsAreParsed(): void
	{
		$xml		= self::readFixture( 'extensions-full.rss' );
		$channel	= Parser::parse( $xml );
		self::assertNotNull( $channel->getExtension( \CeusMedia\RSS\Extension\ITunes\ChannelData::class ) );
		self::assertNotNull( $channel->getItems()[0]->getExtension( \CeusMedia\RSS\Extension\Content\ItemData::class ) );

		$channel	= Parser::parse( $xml, \CeusMedia\RSS\Extension\Extensions::none() );
		self::assertSame( [], $channel->getExtensions() );
		self::assertSame( [], $channel->getItems()[0]->getExtensions() );
	}

	public function testParseRootAndParseDate(): void
	{
		$document	= new \DOMDocument();
		$document->loadXML( self::readFixture( 'minimal.rss' ) );
		self::assertNotNull( $document->documentElement );
		self::assertSame( 'Test 1', Parser::parseRoot( $document->documentElement )->getTitle() );
		self::assertSame( '2024-03-07T09:05:00+00:00', Parser::parseDate( 'Thu, 07 Mar 2024 09:05:00 +0000' )?->format( DATE_ATOM ) );
	}

	public function testAttributesAreTrimmed(): void
	{
		$item	= self::parseItem( '<title>x</title><enclosure url=" https://e.org/a.mp3 " length=" 12 " type=" audio/mpeg "/>'.
			'<guid isPermaLink=" false ">id</guid><source url=" https://e.org/rss ">S</source>' );
		self::assertEquals( new Model\Enclosure( 'https://e.org/a.mp3', 12, 'audio/mpeg' ), $item->getEnclosure() );
		self::assertFalse( $item->getGuid()?->isPermaLink );
		self::assertSame( 'https://e.org/rss', $item->getSource()?->url );
	}

	public function testLowercaseTextInputAndUppercaseDays(): void
	{
		$channel	= Parser::parse( self::wrapChannel( '<textinput><title>t</title><description>d</description><name>n</name><link>l</link></textinput>'.
			'<skipDays><day>SATURDAY</day></skipDays>' ) );
		self::assertSame( 'n', $channel->getTextInput()?->name );
		self::assertSame( ['Saturday'], $channel->getSkipDays() );
	}

	protected static function parseItem( string $itemXml ): Item
	{
		$items	= Parser::parse( self::wrapChannel( '<item>'.$itemXml.'</item>' ) )->getItems();
		self::assertCount( 1, $items );
		return $items[0];
	}

	protected static function readFixture( string $fileName ): string
	{
		$content	= file_get_contents( __DIR__.'/fixtures/'.$fileName );
		self::assertIsString( $content );
		return $content;
	}

	protected static function wrapChannel( string $content ): string
	{
		return '<rss version="2.0"><channel><title>T</title><link>https://example.org/</link><description>D</description>'.$content.'</channel></rss>';
	}
}
