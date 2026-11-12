<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\AtomParser;
use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Extension;
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Extension\Atom\Link;
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;
use CeusMedia\RSS\Extension\DublinCore\Data as DublinCoreData;
use CeusMedia\RSS\Extension\ITunes;
use CeusMedia\RSS\FeedParser;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Parser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( AtomParser::class )]
#[CoversClass( FeedParser::class )]
#[UsesClass( Dom::class )]
#[UsesClass( Parser::class )]
#[UsesClass( Extension\AbstractExtension::class )]
#[UsesClass( Extension\Extensions::class )]
#[UsesClass( Extension\Atom\AtomExtension::class )]
#[UsesClass( AtomData::class )]
#[UsesClass( Link::class )]
#[UsesClass( ContentData::class )]
#[UsesClass( DublinCoreData::class )]
#[UsesClass( ITunes\ITunesExtension::class )]
#[UsesClass( ITunes\ChannelData::class )]
#[UsesClass( ITunes\ItemData::class )]
#[UsesClass( Model\Category::class )]
#[UsesClass( Model\Channel::class )]
#[UsesClass( Model\Enclosure::class )]
#[UsesClass( Model\Guid::class )]
#[UsesClass( Model\Image::class )]
#[UsesClass( Model\Item::class )]
#[UsesClass( Model\Person::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
#[UsesClass( \CeusMedia\RSS\Model\Cloud::class )]
#[UsesClass( \CeusMedia\RSS\Model\Source::class )]
#[UsesClass( \CeusMedia\RSS\Model\TextInput::class )]
class AtomParserTest extends TestCase
{
	public function testParseFeed(): void
	{
		$channel	= AtomParser::parse( self::readFixture( 'atom.xml' ) );
		self::assertSame( 'Example Atom Feed', $channel->getTitle() );
		self::assertSame( 'News &amp; releases', $channel->getDescription() );
		self::assertSame( 'https://atom.example.org/', $channel->getLink() );
		self::assertSame( '2024-03-07T10:00:00+01:00', $channel->getLastBuildDate()?->format( DATE_ATOM ) );
		self::assertSame( '© 2024 Example', $channel->getCopyright() );
		self::assertSame( 'Example Generator', $channel->getGenerator() );
		self::assertEquals( [new Model\Category( 'technology', 'https://atom.example.org/categories' )], $channel->getCategories() );
		self::assertSame( 'https://atom.example.org/logo.png', $channel->getImage()?->getUrl() );
		self::assertEquals( new Model\Person( 'editor@atom.example.org', 'Erika Editor' ), $channel->getManagingEditor() );
		self::assertSame( ['Erika Editor', 'Anna Author'], $channel->getExtension( DublinCoreData::class )?->getCreators() );
		self::assertSame( 'https://atom.example.org/feed.atom', $channel->getExtension( AtomData::class )?->getSelfUrl() );
		self::assertFalse( $channel->getExtension( ITunes\ChannelData::class )?->getExplicit() );
		self::assertNull( $channel->getDocs() );
	}

	public function testParseEntries(): void
	{
		$items	= AtomParser::parse( self::readFixture( 'atom.xml' ) )->getItems();
		self::assertCount( 2, $items );

		self::assertSame( 'Release 2.0', $items[0]->getTitle() );
		self::assertSame( 'https://atom.example.org/release-2-0', $items[0]->getLink() );
		self::assertSame( 'Short summary', $items[0]->getDescription() );
		self::assertSame( '<p>Full <strong>content</strong></p>', $items[0]->getExtension( ContentData::class )?->encoded );
		self::assertEquals( new Model\Enclosure( 'https://atom.example.org/release-2-0.mp3', 1234, 'audio/mpeg' ), $items[0]->getEnclosure() );
		self::assertEquals( new Model\Guid( 'https://atom.example.org/release-2-0', TRUE ), $items[0]->getGuid() );
		self::assertSame( '2024-03-06T17:00:00+01:00', $items[0]->getPubDate()?->format( DATE_ATOM ) );
		self::assertEquals( new Model\Person( 'anna@atom.example.org', 'Anna Author' ), $items[0]->getAuthor() );
		self::assertEquals( [new Model\Category( 'releases' )], $items[0]->getCategories() );
		self::assertSame( 600, $items[0]->getExtension( ITunes\ItemData::class )?->getDuration() );

		//  no summary: content is used as description, no published: updated is used
		self::assertSame( '<p>HTML content</p>', $items[1]->getDescription() );
		self::assertEquals( new Model\Guid( 'tag:atom.example.org,2024:2', FALSE ), $items[1]->getGuid() );
		self::assertSame( '2024-03-01T12:00:00+00:00', $items[1]->getPubDate()?->format( DATE_ATOM ) );
		self::assertNull( $items[1]->getLink() );
	}

	public function testMinimalAndUnusualFeed(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:x="urn:x"><title>T</title><icon>https://e.org/i.ico</icon>'.
			'<link rel="alternate" href="https://e.org/"/><link rel="alternate" href="https://e.org/other"/><link/>'.
			'<author><email> </email></author><author><email>invalid</email><name>N</name></author><category/><x:unknown/>'.
			'<entry><title> </title><link rel="related" href="https://e.org/r"/><link/><content>x</content><author><name> </name></author>'.
			'<x:unknown/><category term=""/></entry></feed>';
		$channel	= AtomParser::parse( $xml );
		self::assertSame( 'https://e.org/', $channel->getLink() );
		self::assertSame( 'https://e.org/i.ico', $channel->getImage()?->getUrl() );
		self::assertEquals( [new Link( 'https://e.org/other' )], $channel->getExtension( AtomData::class )?->getLinks() );
		self::assertEquals( new Model\Person( 'invalid', 'N' ), $channel->getManagingEditor() );
		self::assertSame( [], $channel->getCategories() );
		$item	= $channel->getItems()[0];
		self::assertNull( $item->getTitle() );
		self::assertNull( $item->getLink() );
		self::assertNull( $item->getGuid() );
		self::assertNull( $item->getExtension( DublinCoreData::class ) );
		self::assertSame( 'x', $item->getDescription() );
	}

	public function testFirstAuthorAndEnclosureWin(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom"><author><email>a@e.org</email></author><author><email>b@e.org</email></author>'.
			'<entry><author><email>c@e.org</email></author><author><email>d@e.org</email></author>'.
			'<link rel="enclosure" href="https://e.org/1.mp3"/><link rel="enclosure" href="https://e.org/2.mp3" length="5" type="audio/mpeg"/>'.
			'<link href="https://e.org/first"/><link href="https://e.org/second"/><category term=" t "/></entry></feed>';
		$channel	= AtomParser::parse( $xml );
		self::assertSame( 'a@e.org', $channel->getManagingEditor()?->email );
		$item	= $channel->getItems()[0];
		self::assertSame( 'c@e.org', $item->getAuthor()?->email );
		self::assertEquals( new Model\Enclosure( 'https://e.org/1.mp3', 0, 'application/octet-stream' ), $item->getEnclosure() );
		self::assertSame( 'https://e.org/first', $item->getLink() );
		self::assertSame( 't', $item->getCategories()[0]->name ?? NULL );
		self::assertNull( $item->getExtension( DublinCoreData::class ) );
	}

	public function testEntryAuthorNamesBecomeCreators(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom"><entry><title>x</title><author><name>A</name></author><author><name>B</name><email>b@e.org</email></author></entry></feed>';
		$item	= AtomParser::parse( $xml )->getItems()[0];
		self::assertSame( ['A', 'B'], $item->getExtension( DublinCoreData::class )?->getCreators() );
		self::assertSame( 'b@e.org', $item->getAuthor()?->email );
	}

	public function testXhtmlTextWithoutDivAndWhitespace(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom"><entry><title type="xhtml">  <b xmlns="http://www.w3.org/1999/xhtml">Bold</b> text  </title></entry></feed>';
		self::assertSame( '<b>Bold</b> text', AtomParser::parse( $xml )->getItems()[0]->getTitle() );
	}

	public function testExtensionsCanBeDisabled(): void
	{
		$channel	= AtomParser::parse( self::readFixture( 'atom.xml' ), Extension\Extensions::none() );
		self::assertNull( $channel->getExtension( ITunes\ChannelData::class ) );
		self::assertNull( $channel->getItems()[0]->getExtension( ITunes\ItemData::class ) );
	}

	/**
	 *	@return		array<string, array{string}>
	 */
	public static function unsupportedRootProvider(): array
	{
		return [
			'namespaced rss'			=> ['<rss xmlns="urn:x" version="2.0"/>'],
			'feed without namespace'	=> ['<feed><title>T</title></feed>'],
			'other element in Atom NS'	=> ['<entry xmlns="http://www.w3.org/2005/Atom"/>'],
		];
	}

	#[DataProvider( 'unsupportedRootProvider' )]
	public function testFeedParserRejectsWrongRoots( string $xml ): void
	{
		$this->expectException( ParseException::class );
		$this->expectExceptionMessage( 'Unsupported feed format' );
		FeedParser::detect( $xml );
	}

	public function testExtensionElementBeforeAtomElements(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:x="urn:x"><x:first/><title>Feed</title>'.
			'<entry><x:first/><title>Entry</title></entry></feed>';
		$channel	= AtomParser::parse( $xml );
		self::assertSame( 'Feed', $channel->getTitle() );
		self::assertSame( 'Entry', $channel->getItems()[0]->getTitle() );
	}

	public function testLogoWinsOverIcon(): void
	{
		$xml	= '<feed xmlns="http://www.w3.org/2005/Atom"><logo>https://e.org/logo.png</logo><icon>https://e.org/i.ico</icon><logo> </logo></feed>';
		self::assertSame( 'https://e.org/logo.png', AtomParser::parse( $xml )->getImage()?->getUrl() );
	}

	public function testNotAtomThrowsException(): void
	{
		$this->expectException( ParseException::class );
		$this->expectExceptionMessage( 'Not an Atom feed: root element is <rss>' );
		AtomParser::parse( '<rss version="2.0"/>' );
	}

	public function testFeedWithoutNamespaceIsNotAtom(): void
	{
		$this->expectException( ParseException::class );
		AtomParser::parse( '<feed><title>T</title></feed>' );
	}

	/**
	 *	@return		array<string, array{string, string}>
	 */
	public static function formatProvider(): array
	{
		return [
			'RSS'	=> ['full.rss', FeedParser::FORMAT_RSS],
			'Atom'	=> ['atom.xml', FeedParser::FORMAT_ATOM],
		];
	}

	#[DataProvider( 'formatProvider' )]
	public function testFeedParserDetectsFormat( string $fixture, string $format ): void
	{
		$xml	= self::readFixture( $fixture );
		self::assertSame( $format, FeedParser::detect( $xml ) );
		self::assertNotSame( '', FeedParser::parse( $xml )->getTitle() );
	}

	public function testFeedParserParsesBothFormats(): void
	{
		self::assertSame( 'Example Channel', FeedParser::parse( self::readFixture( 'full.rss' ) )->getTitle() );
		self::assertSame( 'Example Atom Feed', FeedParser::parse( self::readFixture( 'atom.xml' ) )->getTitle() );
	}

	public function testFeedParserRejectsUnsupportedFormats(): void
	{
		$this->expectException( ParseException::class );
		$this->expectExceptionMessage( 'Unsupported feed format: root element is <rdf:RDF>' );
		FeedParser::parse( '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"/>' );
	}

	protected static function readFixture( string $fileName ): string
	{
		$content	= file_get_contents( __DIR__.'/fixtures/'.$fileName );
		self::assertIsString( $content );
		return $content;
	}
}
