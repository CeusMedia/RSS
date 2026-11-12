<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Exception\RenderException;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Renderer;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass( Renderer::class )]
#[UsesClass( Parser::class )]
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
#[UsesClass( \CeusMedia\RSS\Extension\AbstractExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\AtomExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Extensions::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ITunesExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\ChannelData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\Link::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ItemData::class )]
class RendererTest extends TestCase
{
	public function testRenderMinimal(): void
	{
		$channel	= self::createChannel( 'Test 1', 'https://ceusmedia.de/', 'Description of channel' )
			->setLastBuildDate( new DateTimeImmutable( 'Sat, 24 Oct 2020 20:24:46 +0000' ) )
			->setImage( new Image( 'https://example.org/image1', 'Example Image 1' ) )
			->addItem( ( new Item( 'Test 1-1', 'https://example.org/test1/test1-1' ) )->addCategory( 'Test' ) );
		self::assertSame( self::readFixture( 'minimal.rss' ), Renderer::render( $channel ) );
	}

	public function testRoundTripOfFullFeed(): void
	{
		$expected	= self::readFixture( 'full.rss' );
		self::assertSame( $expected, Renderer::render( Parser::parse( $expected ) ) );
	}

	public function testRoundTripOfMinimalFeed(): void
	{
		$expected	= self::readFixture( 'minimal.rss' );
		self::assertSame( $expected, Renderer::render( Parser::parse( $expected ) ) );
	}

	public function testLastBuildDateDefaultsToNow(): void
	{
		$before	= time();
		$xml	= Renderer::render( self::createChannel()->setLastBuildDate( NULL ) );
		$after	= time();
		self::assertSame( 1, preg_match( '@<lastBuildDate>(.+)</lastBuildDate>@', $xml, $matches ) );
		$timestamp	= strtotime( $matches[1] ?? '' );
		self::assertGreaterThanOrEqual( $before, $timestamp );
		self::assertLessThanOrEqual( $after, $timestamp );
	}

	public function testDatesKeepTheirTimezone(): void
	{
		$date		= new DateTimeImmutable( '2024-03-07 09:05:00-05:00' );
		$channel	= self::createChannel()->setPubDate( $date )->addItem( ( new Item( 'x' ) )->setPubDate( $date ) );
		$xml		= Renderer::render( $channel );
		self::assertSame( 2, substr_count( $xml, '<pubDate>Thu, 07 Mar 2024 09:05:00 -0500</pubDate>' ) );
	}

	public function testVersionIs20(): void
	{
		self::assertStringContainsString( '<rss version="2.0">', Renderer::render( self::createChannel() ) );
	}

	public function testDocs(): void
	{
		self::assertStringContainsString( '<docs>'.Channel::DEFAULT_DOCS.'</docs>', Renderer::render( self::createChannel() ) );
		$xml	= Renderer::render( self::createChannel()->setDocs( 'https://example.org/docs' ) );
		self::assertStringContainsString( '<docs>https://example.org/docs</docs>', $xml );
		self::assertStringNotContainsString( '<docs', Renderer::render( self::createChannel()->setDocs( NULL ) ) );
	}

	/**
	 *	@return		array<string, array{string, string, string, string}>
	 */
	public static function missingChannelDataProvider(): array
	{
		return [
			'title empty'			=> [' ', 'l', 'd', 'Channel title cannot be empty'],
			'link empty'			=> ['t', '', 'd', 'Channel link cannot be empty'],
			'description empty'		=> ['t', 'l', "\n", 'Channel description cannot be empty'],
		];
	}

	#[DataProvider( 'missingChannelDataProvider' )]
	public function testMissingRequiredChannelDataThrowsException( string $title, string $link, string $description, string $message ): void
	{
		$this->expectException( RenderException::class );
		$this->expectExceptionMessage( $message );
		Renderer::render( new Channel( $title, $link, $description ) );
	}

	public function testRenderExceptionIsRuntimeException(): void
	{
		$this->expectException( RuntimeException::class );
		Renderer::render( new Channel() );
	}

	public function testSpecialCharactersAreEscaped(): void
	{
		$channel	= self::createChannel( 'A & B <C> "D" \'E\' ]]>' )
			->setCloud( new Model\Cloud( 'a"b<c>&d', 80, '/', 'p', 'soap' ) )
			->addItem( ( new Item( 'Item & more' ) )
				->addCategory( new Model\Category( 'C&D', 'https://example.org/?a=1&b="2"' ) )
				->setSource( new Model\Source( 'Source', 'https://example.org/rss?a=1&b=2' ) )
			);
		$parsed		= Parser::parse( Renderer::render( $channel ) );
		self::assertSame( 'A & B <C> "D" \'E\' ]]>', $parsed->getTitle() );
		self::assertSame( 'a"b<c>&d', $parsed->getCloud()?->domain );
		self::assertEquals( new Model\Category( 'C&D', 'https://example.org/?a=1&b="2"' ), $parsed->getItems()[0]->getCategories()[0] );
		self::assertEquals( new Model\Source( 'Source', 'https://example.org/rss?a=1&b=2' ), $parsed->getItems()[0]->getSource() );
	}

	public function testInvalidXmlCharactersAreRemoved(): void
	{
		$channel	= self::createChannel()
			->addItem( new Item( "A\x00B\x0BC\x1FD", NULL, "Tab\tand\nnewline" ) );
		$item		= Parser::parse( Renderer::render( $channel ) )->getItems()[0];
		self::assertSame( 'ABCD', $item->getTitle() );
		self::assertSame( "Tab\tand\nnewline", $item->getDescription() );
	}

	public function testInvalidUtf8IsSubstituted(): void
	{
		$xml	= Renderer::render( self::createChannel( "Gr\xC3\xB6\xC3\x9Fe \xFF" ) );
		self::assertSame( "Größe \u{FFFD}", Parser::parse( $xml )->getTitle() );
	}

	public function testUnicodeIsKeptLiterally(): void
	{
		$xml	= Renderer::render( self::createChannel( 'Größe € 😀' ) );
		self::assertStringContainsString( '<title>Größe € 😀</title>', $xml );
		self::assertStringStartsWith( '<?xml version="1.0" encoding="UTF-8"?>', $xml );
	}

	public function testItemWithDescriptionOnly(): void
	{
		$xml	= Renderer::render( self::createChannel()->addItem( new Item( NULL, NULL, 'Only content' ) ) );
		self::assertStringContainsString( "<item>\n      <description>Only content</description>\n    </item>", $xml );
	}

	public function testItemWithoutTitleAndDescriptionThrowsException(): void
	{
		$this->expectException( RenderException::class );
		$this->expectExceptionMessage( 'Item #2 must have a title or a description' );
		Renderer::render( self::createChannel()
			->addItem( new Item( 'Valid' ) )
			->addItem( new Item( ' ', 'https://example.org/1' ) ) );
	}

	/**
	 *	@return		array<string, array{bool, string}>
	 */
	public static function guidProvider(): array
	{
		return [
			'permalink'		=> [TRUE, '<guid isPermaLink="true">id-1</guid>'],
			'no permalink'	=> [FALSE, '<guid isPermaLink="false">id-1</guid>'],
		];
	}

	#[DataProvider( 'guidProvider' )]
	public function testGuidPermaLinkIsAlwaysExplicit( bool $isPermaLink, string $expected ): void
	{
		$item	= ( new Item( 'x' ) )->setGuid( new Model\Guid( 'id-1', $isPermaLink ) );
		$xml	= Renderer::render( self::createChannel()->addItem( $item ) );
		self::assertStringContainsString( $expected, $xml );
		self::assertSame( $isPermaLink, Parser::parse( $xml )->getItems()[0]->getGuid()?->isPermaLink );
	}

	public function testPersons(): void
	{
		$channel	= self::createChannel()
			->setManagingEditor( new Model\Person( 'editor@example.org' ) )
			->setWebMaster( new Model\Person( 'admin@example.org', 'Admin' ) )
			->addItem( ( new Item( 'x' ) )->setAuthor( new Model\Person( 'author@example.org', '' ) ) );
		$xml	= Renderer::render( $channel );
		self::assertStringContainsString( '<managingEditor>editor@example.org</managingEditor>', $xml );
		self::assertStringContainsString( '<webMaster>admin@example.org (Admin)</webMaster>', $xml );
		self::assertStringContainsString( '<author>author@example.org</author>', $xml );
	}

	public function testEnclosure(): void
	{
		$item	= ( new Item( 'Episode 1' ) )
			->setEnclosure( new Model\Enclosure( 'https://example.org/e1.mp3', 1024, 'audio/mpeg' ) );
		$xml	= Renderer::render( self::createChannel()->addItem( $item ) );
		self::assertStringContainsString( '<enclosure url="https://example.org/e1.mp3" length="1024" type="audio/mpeg"/>', $xml );
	}

	public function testImageFallsBackToChannelTitleAndLink(): void
	{
		$channel	= self::createChannel( 'Channel', 'https://example.org/' )
			->setImage( new Image( 'https://example.org/logo.png' ) );
		$xml	= Renderer::render( $channel );
		$expected	= "<image>\n      <url>https://example.org/logo.png</url>\n      <title>Channel</title>\n      <link>https://example.org/</link>\n    </image>";
		self::assertStringContainsString( $expected, $xml );
	}

	public function testEmptyOptionalValuesAreOmitted(): void
	{
		$channel	= self::createChannel()
			->setLanguage( '' )
			->setCopyright( ' ' )
			->setGenerator( '' )
			->setRating( '' )
			->setImage( ( new Image( 'u', 't', 'l' ) )->setDescription( '' ) )
			->addItem( ( new Item( 'x', '' ) )->setComments( '' ) );
		$xml	= Renderer::render( $channel );
		foreach( ['language', 'copyright', 'generator', 'rating', 'width', 'height', 'comments', 'description/'] as $name )
			self::assertStringNotContainsString( '<'.$name, $xml );
		self::assertStringNotContainsString( '<link/>', $xml );
	}

	public function testExtensionDataIsRendered(): void
	{
		$item		= ( new Item( 'x' ) )->setExtension( new \CeusMedia\RSS\Extension\Content\ItemData( '<p>Full</p>' ) );
		$channel	= self::createChannel()
			->setExtension( \CeusMedia\RSS\Extension\Atom\ChannelData::self( 'https://example.org/feed.rss' ) )
			->addItem( $item );
		$xml	= Renderer::render( $channel );
		self::assertStringContainsString( '<rss xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">', $xml );
		self::assertStringContainsString( "<docs>https://www.rssboard.org/rss-specification</docs>\n    <atom:link", $xml );
		self::assertStringContainsString( "<title>x</title>\n      <content:encoded>&lt;p&gt;Full&lt;/p&gt;</content:encoded>", $xml );
	}

	public function testExtensionsCanBeDisabled(): void
	{
		$channel	= self::createChannel()->addItem( ( new Item( 'x' ) )->setExtension( new \CeusMedia\RSS\Extension\Content\ItemData( 'y' ) ) );
		$xml		= Renderer::render( $channel, FALSE, \CeusMedia\RSS\Extension\Extensions::none() );
		self::assertStringContainsString( '<rss version="2.0">', $xml );
		self::assertStringNotContainsString( 'content:', $xml );
	}

	public function testUseTabs(): void
	{
		$xml	= Renderer::render( self::createChannel()->addItem( new Item( 'x' ) ), TRUE );
		self::assertStringContainsString( "\n\t<channel>\n\t\t<title>", $xml );
		self::assertStringContainsString( "\n\t\t<item>\n\t\t\t<title>x</title>", $xml );
		self::assertStringNotContainsString( '  ', $xml );
	}

	protected static function createChannel( string $title = 'T', string $link = 'https://example.org/', string $description = 'D' ): Channel
	{
		return ( new Channel( $title, $link, $description ) )
			->setLastBuildDate( new DateTimeImmutable( '@0' ) );
	}

	protected static function readFixture( string $fileName ): string
	{
		$content	= file_get_contents( __DIR__.'/fixtures/'.$fileName );
		self::assertIsString( $content );
		return $content;
	}
}
