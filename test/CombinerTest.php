<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Renderer;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Combiner::class )]
#[UsesClass( Parser::class )]
#[UsesClass( Renderer::class )]
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
#[UsesClass( \CeusMedia\RSS\FeedParser::class )]
#[UsesClass( \CeusMedia\RSS\AtomParser::class )]
#[UsesClass( \CeusMedia\RSS\Filter::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Extensions::class )]
#[UsesClass( \CeusMedia\RSS\Extension\AbstractExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\AtomExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\ChannelData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\Link::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ItemData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\Data::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ITunesExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ChannelData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\ITunes\ItemData::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
class CombinerTest extends TestCase
{
	public function testCombineSortsNewestFirstAndUndatedLast(): void
	{
		$combiner	= ( new Combiner() )
			->addChannel( self::createChannel( 'A', ['a1' => 100, 'a2' => NULL, 'a3' => 300, 'a4' => -100] ) )
			->addChannel( self::createChannel( 'B', ['b1' => 200, 'b2' => 300, 'b3' => NULL] ) );
		self::assertSame( ['a3', 'b2', 'b1', 'a1', 'a4', 'a2', 'b3'], self::titles( $combiner->combine() ) );
	}

	public function testCombineComparesAcrossTimezones(): void
	{
		$channel	= ( new Channel( 'A', 'l', 'd' ) )
			->addItem( ( new Item( 'Berlin 10:00' ) )->setPubDate( new DateTimeImmutable( '2024-01-01 10:00:00+01:00' ) ) )
			->addItem( ( new Item( 'UTC 09:30' ) )->setPubDate( new DateTimeImmutable( '2024-01-01 09:30:00+00:00' ) ) );
		self::assertSame( ['UTC 09:30', 'Berlin 10:00'], self::titles( ( new Combiner() )->addChannel( $channel )->combine() ) );
	}

	public function testCombineWithLimit(): void
	{
		$combiner	= ( new Combiner() )
			->addChannel( self::createChannel( 'A', ['a1' => 100, 'a2' => 200, 'a3' => 300] ) );
		self::assertSame( ['a3', 'a2'], self::titles( $combiner->combine( 2 ) ) );
		self::assertCount( 3, $combiner->combine( 0 ) );
		self::assertCount( 3, $combiner->combine( 10 ) );
	}

	public function testCombineWithoutChannels(): void
	{
		self::assertSame( [], ( new Combiner() )->combine() );
	}

	public function testAddXml(): void
	{
		$xml		= (string) file_get_contents( __DIR__.'/fixtures/full.rss' );
		$combiner	= ( new Combiner() )->add( $xml );
		self::assertSame( ['First item', 'Second item', NULL], self::titles( $combiner->combine() ) );
	}

	public function testAddInvalidXmlThrowsException(): void
	{
		$this->expectException( ParseException::class );
		( new Combiner() )->add( '<feed/>' );
	}

	public function testRenderDoesNotModifyGivenChannel(): void
	{
		$combiner	= ( new Combiner() )
			->addChannel( self::createChannel( 'A', ['a1' => 100, 'a2' => 200] ) );
		$target		= self::createChannel( 'Target', ['existing' => 50] );

		$xml1	= $combiner->render( $target );
		$xml2	= $combiner->render( $target );

		self::assertCount( 1, $target->getItems() );
		self::assertSame( $xml1, $xml2 );
		self::assertSame( ['existing', 'a2', 'a1'], self::titles( Parser::parse( $xml1 )->getItems() ) );
	}

	public function testRenderUsesSpacesAndAllItems(): void
	{
		$combiner	= ( new Combiner() )->addChannel( self::createChannel( 'A', ['a1' => 100, 'a2' => 200] ) );
		$xml		= $combiner->render( self::createChannel( 'Target', [] ) );
		self::assertStringContainsString( "\n  <channel>", $xml );
		self::assertCount( 2, Parser::parse( $xml )->getItems() );
	}

	public function testRenderWithLimit(): void
	{
		$combiner	= ( new Combiner() )
			->addChannel( self::createChannel( 'A', ['a1' => 100, 'a2' => 200, 'a3' => 300] ) );
		$items		= Parser::parse( $combiner->render( self::createChannel( 'Target', [] ), 2 ) )->getItems();
		self::assertSame( ['a3', 'a2'], self::titles( $items ) );
	}

	public function testAddAtomFeed(): void
	{
		$xml		= (string) file_get_contents( __DIR__.'/fixtures/atom.xml' );
		$combiner	= ( new Combiner() )->add( $xml );
		self::assertSame( ['Release 2.0', 'Only updated'], self::titles( $combiner->combine() ) );
		self::assertCount( 1, $combiner->getChannels() );
	}

	public function testDuplicatesAreRemoved(): void
	{
		$older		= ( new Item( 'Older copy', 'https://example.org/1' ) )->setPubDate( new DateTimeImmutable( '@100' ) );
		$newer		= ( new Item( 'Newer copy', 'https://example.org/1' ) )->setPubDate( new DateTimeImmutable( '@200' ) );
		$byGuid1	= ( new Item( 'Guid A', 'https://example.org/a' ) )->setGuid( new Model\Guid( 'id-1', FALSE ) )->setPubDate( new DateTimeImmutable( '@50' ) );
		$byGuid2	= ( new Item( 'Guid B', 'https://example.org/b' ) )->setGuid( new Model\Guid( 'id-1', FALSE ) )->setPubDate( new DateTimeImmutable( '@40' ) );
		$noId1		= new Item( 'No identity 1' );
		$noId2		= new Item( 'No identity 2' );
		$combiner	= ( new Combiner() )
			->addChannel( ( new Channel( 'A', 'l', 'd' ) )->addItem( $older )->addItem( $byGuid1 )->addItem( $noId1 ) )
			->addChannel( ( new Channel( 'B', 'l', 'd' ) )->addItem( $newer )->addItem( $byGuid2 )->addItem( $noId2 ) );
		self::assertSame( ['Newer copy', 'Guid A', 'No identity 1', 'No identity 2'], self::titles( $combiner->combine() ) );
		self::assertCount( 6, $combiner->setUnique( FALSE )->combine() );
	}

	public function testLimitCountsUniqueItems(): void
	{
		$channel	= new Channel( 'A', 'l', 'd' );
		foreach( [300, 300, 200, 100] as $nr => $timestamp )
			$channel->addItem( ( new Item( 'Item '.$nr, 'https://example.org/'.$timestamp ) )->setPubDate( new DateTimeImmutable( '@'.$timestamp ) ) );
		self::assertSame( ['Item 0', 'Item 2'], self::titles( ( new Combiner() )->addChannel( $channel )->combine( 2 ) ) );
	}

	public function testSourceIsAdded(): void
	{
		$withSource	= ( new Item( 'Has source' ) )->setSource( new Model\Source( 'Original', 'https://original.example.org/rss' ) );
		$channel	= ( new Channel( 'Feed A', 'l', 'd' ) )->addItem( new Item( 'Plain' ) )->addItem( $withSource );
		$combiner	= ( new Combiner() )->addChannel( $channel, 'https://a.example.org/feed.rss' );
		$items		= $combiner->combine();
		self::assertEquals( new Model\Source( 'Feed A', 'https://a.example.org/feed.rss' ), $items[0]->getSource() );
		self::assertSame( $withSource, $items[1] );
		self::assertNull( $channel->getItems()[0]->getSource(), 'original item must not be modified' );

		self::assertNull( $combiner->setAddSource( FALSE )->combine()[0]->getSource() );
	}

	public function testSourceFromAtomSelfLink(): void
	{
		$channel	= ( new Channel( 'Feed B', 'l', 'd' ) )
			->setExtension( \CeusMedia\RSS\Extension\Atom\ChannelData::self( 'https://b.example.org/feed.rss' ) )
			->addItem( new Item( 'x' ) );
		$items	= ( new Combiner() )->addChannel( $channel )->combine();
		self::assertSame( 'https://b.example.org/feed.rss', $items[0]->getSource()?->url );
		self::assertNull( ( new Combiner() )->addChannel( new Channel( 'C', 'l', 'd' ) )->addChannel( ( new Channel( 'D', 'l', 'd' ) )->addItem( new Item( 'y' ) ) )->combine()[0]->getSource() );
	}

	public function testFilters(): void
	{
		$channel	= ( new Channel( 'A', 'l', 'd' ) )
			->addItem( ( new Item( 'Release 2.0' ) )->addCategory( 'Releases' )->setPubDate( new DateTimeImmutable( '2024-03-06' ) ) )
			->addItem( ( new Item( 'Maintenance', NULL, 'Release notes follow' ) )->addCategory( 'Ops' )->setPubDate( new DateTimeImmutable( '2024-03-04' ) ) )
			->addItem( ( new Item( 'Welcome' ) )->setPubDate( new DateTimeImmutable( '2024-02-15' ) ) );
		$combiner	= static fn(): Combiner => ( new Combiner() )->addChannel( $channel );

		self::assertSame( ['Release 2.0'], self::titles( $combiner()->addFilter( \CeusMedia\RSS\Filter::category( 'releases' ) )->combine() ) );
		self::assertSame( ['Release 2.0', 'Maintenance'], self::titles( $combiner()->addFilter( \CeusMedia\RSS\Filter::search( 'RELEASE' ) )->combine() ) );
		self::assertSame( ['Release 2.0', 'Maintenance'], self::titles( $combiner()->addFilter( \CeusMedia\RSS\Filter::since( new DateTimeImmutable( '2024-03-01' ) ) )->combine() ) );
		self::assertSame( ['Welcome'], self::titles( $combiner()->addFilter( \CeusMedia\RSS\Filter::not( \CeusMedia\RSS\Filter::search( 'release' ) ) )->combine() ) );
		self::assertSame( ['Maintenance'], self::titles( $combiner()
			->addFilter( \CeusMedia\RSS\Filter::search( 'release' ) )
			->addFilter( \CeusMedia\RSS\Filter::category( 'ops', 'other' ) )
			->combine() ) );
		self::assertSame( [], self::titles( $combiner()->addFilter( static fn( Item $item, Channel $channel ): bool => 'B' === $channel->getTitle() )->combine() ) );
	}

	/**
	 *	@param		array<string, ?int>		$items		Map of item titles and timestamps
	 */
	protected static function createChannel( string $title, array $items ): Channel
	{
		$channel	= ( new Channel( $title, 'https://example.org/'.$title, 'Channel '.$title ) )
			->setLastBuildDate( new DateTimeImmutable( '@0' ) );
		foreach( $items as $itemTitle => $timestamp ){
			$date	= NULL !== $timestamp ? new DateTimeImmutable( '@'.$timestamp ) : NULL;
			$channel->addItem( ( new Item( $itemTitle ) )->setPubDate( $date ) );
		}
		return $channel;
	}

	/**
	 *	@param		list<Item>		$items
	 *	@return		list<?string>
	 */
	protected static function titles( array $items ): array
	{
		return array_map( static fn( Item $item ): ?string => $item->getTitle(), $items );
	}
}
