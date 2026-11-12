<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Extension;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Extension\ITunes\Category;
use CeusMedia\RSS\Extension\ITunes\ChannelData;
use CeusMedia\RSS\Extension\ITunes\ItemData;
use CeusMedia\RSS\Extension\ITunes\ITunesExtension;
use CeusMedia\RSS\Model;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Renderer;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( ITunesExtension::class )]
#[CoversClass( ChannelData::class )]
#[CoversClass( ItemData::class )]
#[CoversClass( Category::class )]
#[UsesClass( AbstractExtension::class )]
#[UsesClass( Extensions::class )]
#[UsesClass( Dom::class )]
#[UsesClass( Parser::class )]
#[UsesClass( Renderer::class )]
#[UsesClass( Model\Channel::class )]
#[UsesClass( Model\Item::class )]
#[UsesClass( Model\Person::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Atom\AtomExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\Content\ContentExtension::class )]
#[UsesClass( \CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension::class )]
class ITunesTest extends TestCase
{
	/**
	 *	@return		array<string, array{string, ?int}>
	 */
	public static function durationProvider(): array
	{
		return [
			'seconds'		=> ['3723', 3723],
			'MM:SS'			=> ['62:03', 3723],
			'H:MM:SS'		=> ['1:02:03', 3723],
			'HH:MM:SS'		=> ['01:02:03', 3723],
			'zero'			=> ['0', 0],
			'invalid'		=> ['about an hour', NULL],
			'empty'			=> ['', NULL],
		];
	}

	#[DataProvider( 'durationProvider' )]
	public function testParseDuration( string $value, ?int $expected ): void
	{
		self::assertSame( $expected, ITunesExtension::parseDuration( $value ) );
	}

	/**
	 *	@return		array<string, array{string, ?bool}>
	 */
	public static function explicitProvider(): array
	{
		return [
			'true'		=> ['true', TRUE],
			'yes'		=> ['Yes', TRUE],
			'explicit'	=> ['explicit', TRUE],
			'false'		=> ['false', FALSE],
			'no'		=> ['no', FALSE],
			'clean'		=> ['Clean', FALSE],
			'invalid'	=> ['maybe', NULL],
		];
	}

	#[DataProvider( 'explicitProvider' )]
	public function testParseExplicit( string $value, ?bool $expected ): void
	{
		self::assertSame( $expected, ITunesExtension::parseExplicit( $value ) );
	}

	public function testRenderAndParseFlags(): void
	{
		$channel	= ( new Channel( 'T', 'https://example.org/', 'D' ) )
			->setLastBuildDate( new DateTimeImmutable( '@0' ) )
			->setExtension( ( new ChannelData() )
				->setBlock( TRUE )
				->setComplete( TRUE )
				->setExplicit( TRUE )
				->setNewFeedUrl( 'https://example.org/new.rss' )
				->setOwner( new Person( 'owner@example.org' ) )
				->setCategories( [new Category( 'Arts' )] )
			)
			->addItem( ( new Item( 'x' ) )->setExtension( ( new ItemData() )->setBlock( TRUE )->setExplicit( TRUE )->setEpisodeType( 'bonus' ) ) );
		$xml	= Renderer::render( $channel );
		foreach( ['<itunes:block>Yes</itunes:block>', '<itunes:complete>Yes</itunes:complete>', '<itunes:explicit>true</itunes:explicit>',
			'<itunes:new-feed-url>https://example.org/new.rss</itunes:new-feed-url>', "<itunes:owner>\n      <itunes:email>owner@example.org</itunes:email>\n    </itunes:owner>",
			'<itunes:episodeType>bonus</itunes:episodeType>'] as $expected )
			self::assertStringContainsString( $expected, $xml );

		$parsed		= Parser::parse( $xml );
		$data		= $parsed->getExtension( ChannelData::class );
		self::assertNotNull( $data );
		self::assertTrue( $data->isBlocked() );
		self::assertTrue( $data->isComplete() );
		self::assertTrue( $data->getExplicit() );
		self::assertSame( 'https://example.org/new.rss', $data->getNewFeedUrl() );
		self::assertEquals( new Person( 'owner@example.org' ), $data->getOwner() );
		$itemData	= $parsed->getItems()[0]->getExtension( ItemData::class );
		self::assertTrue( $itemData?->isBlocked() );
		self::assertTrue( $itemData->getExplicit() );
	}

	public function testSettersAndGetters(): void
	{
		$channelData	= ( new ChannelData() )
			->setTitle( 'Title' )
			->setAuthor( 'Author' )
			->setImage( 'https://example.org/art.jpg' )
			->addCategory( new Category( 'Arts', 'Design' ) );
		self::assertSame( 'Title', $channelData->getTitle() );
		self::assertSame( 'Author', $channelData->getAuthor() );
		self::assertSame( 'https://example.org/art.jpg', $channelData->getImage() );
		self::assertSame( 'Design', $channelData->getCategories()[0]->subcategory ?? NULL );
		self::assertFalse( $channelData->isBlocked() );
		self::assertFalse( $channelData->isComplete() );
		self::assertNull( $channelData->getExplicit() );

		$itemData	= ( new ItemData() )->setTitle( 'Episode' )->setImage( 'https://example.org/e.jpg' );
		self::assertSame( 'Episode', $itemData->getTitle() );
		self::assertSame( 'https://example.org/e.jpg', $itemData->getImage() );
		self::assertFalse( $itemData->isBlocked() );
	}

	public function testOnlyItemOrOnlyChannelData(): void
	{
		$channel	= ( new Channel( 'T', 'https://example.org/', 'D' ) )
			->addItem( ( new Item( 'x' ) )->setExtension( ( new ItemData() )->setDuration( 60 ) ) )
			->addItem( new Item( 'y' ) );
		$xml	= Renderer::render( $channel );
		self::assertSame( 1, substr_count( $xml, '<itunes:' ) );

		$channel	= ( new Channel( 'T', 'https://example.org/', 'D' ) )
			->setExtension( ( new ChannelData() )->setAuthor( 'A' ) )
			->addItem( new Item( 'x' ) );
		$xml	= Renderer::render( $channel );
		self::assertSame( 1, substr_count( $xml, '<itunes:' ) );
	}

	public function testEmptyDataRendersNothing(): void
	{
		$channel	= ( new Channel( 'T', 'https://example.org/', 'D' ) )
			->setExtension( new ChannelData() )
			->addItem( ( new Item( 'x' ) )->setExtension( new ItemData() ) );
		$xml	= Renderer::render( $channel );
		self::assertSame( 0, substr_count( $xml, '<itunes:' ) );
	}

	public function testInvalidValuesAreSkippedOnParsing(): void
	{
		$xml	= '<rss version="2.0" xmlns:itunes="'.ITunesExtension::NAMESPACE_URI.'"><channel><title>T</title><link>l</link><description>d</description>'.
			'<itunes:type>weekly</itunes:type><itunes:category/><itunes:owner><itunes:name>No Mail</itunes:name></itunes:owner><itunes:unknown>x</itunes:unknown>'.
			'<item><title>x</title><itunes:episode>0</itunes:episode><itunes:season>-1</itunes:season><itunes:episodeType>special</itunes:episodeType>'.
			'<itunes:duration>long</itunes:duration><itunes:unknown>x</itunes:unknown></item></channel></rss>';
		$channel	= Parser::parse( $xml );
		$data		= $channel->getExtension( ChannelData::class );
		self::assertNotNull( $data );
		self::assertNull( $data->getType() );
		self::assertSame( [], $data->getCategories() );
		self::assertNull( $data->getOwner() );
		$itemData	= $channel->getItems()[0]->getExtension( ItemData::class );
		self::assertNull( $itemData?->getEpisode() );
		self::assertNull( $itemData?->getSeason() );
		self::assertNull( $itemData?->getEpisodeType() );
		self::assertNull( $itemData?->getDuration() );
	}

	public function testCaseInsensitiveValuesAndTrimmedCategory(): void
	{
		$xml	= '<rss version="2.0" xmlns:itunes="'.ITunesExtension::NAMESPACE_URI.'"><channel><title>T</title><link>l</link><description>d</description>'.
			'<itunes:type>Serial</itunes:type><itunes:category text=" Arts "><itunes:category text=" Design "/></itunes:category>'.
			'<item><title>x</title><itunes:episodeType>Trailer</itunes:episodeType></item></channel></rss>';
		$channel	= Parser::parse( $xml );
		self::assertSame( 'serial', $channel->getExtension( ChannelData::class )?->getType() );
		self::assertEquals( [new Category( 'Arts', 'Design' )], $channel->getExtension( ChannelData::class )->getCategories() );
		self::assertSame( 'trailer', $channel->getItems()[0]->getExtension( ItemData::class )?->getEpisodeType() );
	}

	public function testUnknownOrInvalidElementsCreateNoData(): void
	{
		$xml	= '<rss version="2.0" xmlns:itunes="'.ITunesExtension::NAMESPACE_URI.'"><channel><title>T</title><link>l</link><description>d</description>'.
			'<itunes:unknown>x</itunes:unknown><itunes:type>weekly</itunes:type>'.
			'<item><title>x</title><itunes:unknown>x</itunes:unknown><itunes:episodeType>special</itunes:episodeType></item></channel></rss>';
		$channel	= Parser::parse( $xml );
		self::assertFalse( $channel->hasExtension( ChannelData::class ) );
		self::assertFalse( $channel->getItems()[0]->hasExtension( ItemData::class ) );
	}

	public function testDurationMustMatchCompletely(): void
	{
		self::assertNull( ITunesExtension::parseDuration( 'x1:00' ) );
		self::assertNull( ITunesExtension::parseDuration( '1:00x' ) );
		self::assertSame( 60, ITunesExtension::parseDuration( '1:00' ) );
	}

	public function testSetCategoriesReplaces(): void
	{
		$data	= ( new ChannelData() )->addCategory( new Category( 'A' ) )->setCategories( [new Category( 'B' ), new Category( 'C' )] );
		self::assertSame( ['B', 'C'], array_map( static fn( Category $category ): string => $category->name, $data->getCategories() ) );
	}

	/**
	 *	@return		array<string, array{callable(): mixed, string}>
	 */
	public static function invalidValueProvider(): array
	{
		return [
			'type'			=> [static fn() => ( new ChannelData() )->setType( 'weekly' ), 'iTunes type must be one of: episodic, serial'],
			'episode type'	=> [static fn() => ( new ItemData() )->setEpisodeType( 'special' ), 'iTunes episode type must be one of: full, trailer, bonus'],
			'duration'		=> [static fn() => ( new ItemData() )->setDuration( -1 ), 'iTunes duration cannot be negative'],
			'episode'		=> [static fn() => ( new ItemData() )->setEpisode( 0 ), 'iTunes episode number must be positive'],
			'season'		=> [static fn() => ( new ItemData() )->setSeason( 0 ), 'iTunes season number must be positive'],
			'category'		=> [static fn() => new Category( ' ' ), 'iTunes category cannot be empty'],
		];
	}

	/**
	 *	@param		callable(): mixed		$factory
	 */
	#[DataProvider( 'invalidValueProvider' )]
	public function testInvalidValuesThrowException( callable $factory, string $message ): void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( $message );
		$factory();
	}

	public function testValidBoundaryValues(): void
	{
		$data	= ( new ItemData() )->setDuration( 0 )->setEpisode( 1 )->setSeason( 1 )->setEpisodeType( NULL );
		self::assertSame( 0, $data->getDuration() );
		self::assertSame( 1, $data->getEpisode() );
		self::assertSame( 1, $data->getSeason() );
		self::assertSame( 'serial', ( new ChannelData() )->setType( 'serial' )->getType() );
	}
}
