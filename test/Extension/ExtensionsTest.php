<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Extension;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Extension\Atom;
use CeusMedia\RSS\Extension\Content;
use CeusMedia\RSS\Extension\DublinCore;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Extension\ITunes;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Renderer;
use DateTimeImmutable;
use DOMElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( AbstractExtension::class )]
#[CoversClass( Extensions::class )]
#[CoversClass( Atom\AtomExtension::class )]
#[CoversClass( Atom\ChannelData::class )]
#[CoversClass( Atom\Link::class )]
#[CoversClass( Content\ContentExtension::class )]
#[CoversClass( Content\ItemData::class )]
#[CoversClass( DublinCore\DublinCoreExtension::class )]
#[CoversClass( DublinCore\Data::class )]
#[CoversClass( ITunes\ITunesExtension::class )]
#[UsesClass( Dom::class )]
#[UsesClass( Parser::class )]
#[UsesClass( Renderer::class )]
#[UsesClass( ITunes\Category::class )]
#[UsesClass( ITunes\ChannelData::class )]
#[UsesClass( ITunes\ItemData::class )]
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
class ExtensionsTest extends TestCase
{
	public function testRoundTrip(): void
	{
		$expected	= self::readFixture( 'extensions-full.rss' );
		self::assertSame( $expected, Renderer::render( Parser::parse( $expected ) ) );
	}

	public function testParseAllExtensions(): void
	{
		$channel	= Parser::parse( self::readFixture( 'extensions-full.rss' ) );

		$atom	= $channel->getExtension( Atom\ChannelData::class );
		self::assertNotNull( $atom );
		self::assertSame( 'https://podcast.example.org/feed.rss', $atom->getSelfUrl() );
		self::assertCount( 2, $atom->getLinks() );
		self::assertEquals( [new Atom\Link( 'https://pubsubhubbub.appspot.com/', 'hub' )], $atom->getLinksByRel( 'hub' ) );
		self::assertSame( ['Example Inc.'], $channel->getExtension( DublinCore\Data::class )?->getCreators() );

		$itunes	= $channel->getExtension( ITunes\ChannelData::class );
		self::assertNotNull( $itunes );
		self::assertSame( 'Example Podcast', $itunes->getTitle() );
		self::assertSame( 'Hanna Host', $itunes->getAuthor() );
		self::assertEquals( new Model\Person( 'host@podcast.example.org', 'Hanna Host' ), $itunes->getOwner() );
		self::assertSame( 'https://podcast.example.org/artwork.jpg', $itunes->getImage() );
		self::assertEquals( [new ITunes\Category( 'Technology', 'Software How-To' ), new ITunes\Category( 'Education' )], $itunes->getCategories() );
		self::assertFalse( $itunes->getExplicit() );
		self::assertSame( 'episodic', $itunes->getType() );

		$item	= $channel->getItems()[0];
		self::assertSame( '<p>Show notes with <a href="https://example.org/?a=1&b=2">links</a>.</p>', $item->getExtension( Content\ItemData::class )?->encoded );
		self::assertSame( ['Hanna Host', 'Gustav Guest'], $item->getExtension( DublinCore\Data::class )?->getCreators() );
		$episode	= $item->getExtension( ITunes\ItemData::class );
		self::assertNotNull( $episode );
		self::assertSame( 'Escaping', $episode->getTitle() );
		self::assertSame( 3723, $episode->getDuration() );
		self::assertSame( 'https://podcast.example.org/e2.jpg', $episode->getImage() );
		self::assertFalse( $episode->getExplicit() );
		self::assertSame( 1, $episode->getSeason() );
		self::assertSame( 2, $episode->getEpisode() );
		self::assertSame( 'full', $episode->getEpisodeType() );
	}

	public function testNamespacesAreOnlyDeclaredIfUsed(): void
	{
		$xml	= Renderer::render( self::createChannel() );
		self::assertStringContainsString( '<rss version="2.0">', $xml );

		$channel	= self::createChannel();
		$channel->getItems()[0]->setExtension( new Content\ItemData( '<p>x</p>' ) );
		$xml	= Renderer::render( $channel );
		self::assertStringContainsString( '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">', $xml );
		self::assertSame( 1, substr_count( $xml, 'xmlns:' ) );
	}

	public function testExtensionsCanBeDisabled(): void
	{
		$channel	= Parser::parse( self::readFixture( 'extensions-full.rss' ), Extensions::none() );
		self::assertSame( [], $channel->getExtensions() );
		self::assertSame( [], $channel->getItems()[0]->getExtensions() );

		$channel	= Parser::parse( self::readFixture( 'extensions-full.rss' ) );
		$xml		= Renderer::render( $channel, FALSE, Extensions::none() );
		self::assertStringNotContainsString( 'xmlns:', $xml );
		self::assertStringNotContainsString( 'itunes:', $xml );
	}

	public function testCustomExtension(): void
	{
		$extension	= new class extends AbstractExtension{
			public function getNamespace(): string
			{
				return 'https://podcastindex.org/namespace/1.0';
			}

			public function getPrefix(): string
			{
				return 'podcast';
			}

			public function parseChannelElement( DOMElement $element, Channel $channel ): void
			{
				if( 'locked' === $element->localName )
					$channel->setExtension( new \ArrayObject( ['locked' => Dom::getText( $element )] ) );
			}

			public function renderChannel( Channel $channel, DOMElement $node ): void
			{
				$data	= $channel->getExtension( \ArrayObject::class );
				if( NULL !== $data && is_string( $data['locked'] ?? NULL ) )
					$this->append( $node, 'locked', $data['locked'] );
			}

			protected function getDataClasses(): array
			{
				return [\ArrayObject::class];
			}
		};
		$extensions	= Extensions::default()->add( $extension );
		self::assertSame( $extension, $extensions->get( 'https://podcastindex.org/namespace/1.0' ) );
		self::assertCount( 5, $extensions->getAll() );

		$xml		= '<rss version="2.0" xmlns:podcast="https://podcastindex.org/namespace/1.0"><channel><title>T</title><link>l</link><description>d</description><podcast:locked>yes</podcast:locked></channel></rss>';
		$channel	= Parser::parse( $xml, $extensions );
		$output		= Renderer::render( $channel->setLastBuildDate( new DateTimeImmutable( '@0' ) ), FALSE, $extensions );
		self::assertStringContainsString( 'xmlns:podcast="https://podcastindex.org/namespace/1.0"', $output );
		self::assertStringContainsString( '<podcast:locked>yes</podcast:locked>', $output );
	}

	public function testElementsAtWrongLevelAreIgnored(): void
	{
		$xml	= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">'.
			'<channel><title>T</title><link>l</link><description>d</description><content:encoded>x</content:encoded>'.
			'<item><title>x</title><atom:link href="https://example.org/" rel="self"/></item></channel></rss>';
		$channel	= Parser::parse( $xml );
		self::assertSame( [], $channel->getExtensions() );
		self::assertSame( [], $channel->getItems()[0]->getExtensions() );
	}

	public function testDataConstructors(): void
	{
		self::assertSame( ['A', 'B'], ( new DublinCore\Data( ['A', ' ', 'B'] ) )->getCreators() );
	}

	public function testInvalidLinksThrowException(): void
	{
		foreach( [['', 'self'], ['x', ' '], ['x', 'self', NULL, NULL, -1]] as $arguments ){
			try{
				new Atom\Link( ...$arguments );
				self::fail( 'Expected exception' );
			}
			catch( \InvalidArgumentException ){
				$this->addToAssertionCount( 1 );
			}
		}
	}

	public function testDataOnlyOnSomeLevels(): void
	{
		$channel	= self::createChannel()
			->addItem( ( new Item( 'y' ) )->setExtension( new DublinCore\Data( ['Item Creator'] ) ) );
		$xml	= Renderer::render( $channel );
		self::assertSame( 1, substr_count( $xml, '<dc:creator>' ) );

		$channel	= self::createChannel()->setExtension( new DublinCore\Data( ['Channel Creator'] ) );
		$xml	= Renderer::render( $channel );
		self::assertSame( 1, substr_count( $xml, '<dc:creator>' ) );
	}

	public function testGetAllReturnsList(): void
	{
		$all	= Extensions::default()->getAll();
		self::assertSame( [0, 1, 2, 3], array_keys( $all ) );
		$used	= Extensions::default()->getUsedBy( self::createChannel()->setExtension( new DublinCore\Data( ['x'] ) ) );
		self::assertSame( [0], array_keys( $used ) );
		self::assertInstanceOf( DublinCore\DublinCoreExtension::class, $used[0] );
		self::assertNull( Extensions::default()->get( 'urn:unknown' ) );
	}

	public function testAtomLinkAttributesAreTrimmed(): void
	{
		$xml	= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>T</title><link>l</link><description>d</description>'.
			'<atom:link href=" https://e.org/feed " rel=" self " type=" application/rss+xml " title=" Feed " length=" 0 "/><atom:link href="https://e.org/"/></channel></rss>';
		$links	= Parser::parse( $xml )->getExtension( Atom\ChannelData::class )?->getLinks() ?? [];
		self::assertCount( 2, $links );
		self::assertSame( ['https://e.org/feed', 'self', 'application/rss+xml', 'Feed', 0], [$links[0]->href, $links[0]->rel, $links[0]->type, $links[0]->title, $links[0]->length] );
		self::assertSame( ['https://e.org/', 'alternate', NULL, NULL, NULL], [$links[1]->href, $links[1]->rel, $links[1]->type, $links[1]->title, $links[1]->length] );

		$xml	= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>T</title><link>l</link><description>d</description>'.
			'<atom:link href="https://e.org/" type=" " title=" "/><dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">A</dc:creator>'.
			'<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">B</dc:creator></channel></rss>';
		$channel	= Parser::parse( $xml );
		$link		= $channel->getExtension( Atom\ChannelData::class )?->getLinks()[0];
		self::assertNull( $link?->type );
		self::assertNull( $link?->title );
		self::assertSame( ['A', 'B'], $channel->getExtension( DublinCore\Data::class )?->getCreators() );
	}

	public function testAtomLinkWithWhitespaceHrefIsInvalid(): void
	{
		$this->expectException( \InvalidArgumentException::class );
		new Atom\Link( ' ' );
	}

	public function testAtomSelfLinkFactory(): void
	{
		$channel	= self::createChannel()->setExtension( Atom\ChannelData::self( 'https://example.org/feed.rss' ) );
		$xml		= Renderer::render( $channel );
		self::assertStringContainsString( '<atom:link href="https://example.org/feed.rss" rel="self" type="application/rss+xml"/>', $xml );
	}

	public function testAtomLinkWithLengthAndTitle(): void
	{
		$data	= new Atom\ChannelData( [new Atom\Link( 'https://example.org/a.mp3', 'enclosure', 'audio/mpeg', 'Audio', 123 )] );
		$xml	= Renderer::render( self::createChannel()->setExtension( $data ) );
		self::assertStringContainsString( '<atom:link href="https://example.org/a.mp3" rel="enclosure" type="audio/mpeg" title="Audio" length="123"/>', $xml );
		$parsed	= Parser::parse( $xml )->getExtension( Atom\ChannelData::class );
		self::assertEquals( $data, $parsed );
		self::assertNull( ( new Atom\ChannelData() )->getSelfUrl() );
	}

	public function testInvalidExtensionValuesAreSkipped(): void
	{
		$xml	= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel><title>T</title><link>l</link><description>d</description>'.
			'<atom:link rel="self"/><atom:link href="x" rel="other" length="-1"/><atom:id>ignored</atom:id><dc:creator> </dc:creator><dc:date>2024</dc:date>'.
			'<item><title>x</title><content:encoded> </content:encoded><content:other>y</content:other><dc:creator></dc:creator><dc:date>2024</dc:date></item></channel></rss>';
		$channel	= Parser::parse( $xml );
		//  link without href is skipped, invalid length is ignored
		self::assertEquals( [new Atom\Link( 'x', 'other' )], $channel->getExtension( Atom\ChannelData::class )?->getLinks() );
		self::assertSame( [], $channel->getExtension( DublinCore\Data::class )?->getCreators() ?? [] );
		self::assertNull( $channel->getItems()[0]->getExtension( Content\ItemData::class ) );
		self::assertSame( [], $channel->getItems()[0]->getExtension( DublinCore\Data::class )?->getCreators() ?? [] );
	}

	protected static function createChannel(): Channel
	{
		return ( new Channel( 'T', 'https://example.org/', 'D' ) )
			->setLastBuildDate( new DateTimeImmutable( '@0' ) )
			->addItem( new Item( 'x' ) );
	}

	protected static function readFixture( string $fileName ): string
	{
		$content	= file_get_contents( dirname( __DIR__ ).'/fixtures/'.$fileName );
		self::assertIsString( $content );
		return $content;
	}
}
