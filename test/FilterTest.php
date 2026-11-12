<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest;

use CeusMedia\RSS\Filter;
use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Filter::class )]
#[UsesClass( Category::class )]
#[UsesClass( Channel::class )]
#[UsesClass( Item::class )]
class FilterTest extends TestCase
{
	public function testCategory(): void
	{
		$channel	= new Channel();
		$filter		= Filter::category( ' News ', 'Releases' );
		self::assertTrue( $filter( ( new Item( 'x' ) )->addCategory( 'news' ), $channel ) );
		self::assertTrue( $filter( ( new Item( 'x' ) )->addCategory( 'Other' )->addCategory( 'RELEASES' ), $channel ) );
		self::assertFalse( $filter( ( new Item( 'x' ) )->addCategory( 'Other' ), $channel ) );
		self::assertFalse( $filter( new Item( 'x' ), $channel ) );
	}

	public function testSearch(): void
	{
		$channel	= new Channel();
		$filter		= Filter::search( ' Größe ' );
		self::assertTrue( $filter( new Item( 'Die größe zählt' ), $channel ) );
		self::assertTrue( $filter( new Item( NULL, NULL, '<p>Die <b>Größe</b></p>' ), $channel ) );
		self::assertFalse( $filter( new Item( 'Grö<b>ße</b>' ), $channel ) );
		self::assertFalse( $filter( new Item( 'Anything' ), $channel ) );
	}

	public function testMultibyteCaseInsensitivity(): void
	{
		$channel	= new Channel();
		self::assertTrue( Filter::search( 'ÄRGER' )( new Item( 'Kein ärger' ), $channel ) );
		self::assertTrue( Filter::search( 'ärger' )( new Item( 'KEIN ÄRGER' ), $channel ) );
		self::assertTrue( Filter::category( 'ÜBER' )( ( new Item( 'x' ) )->addCategory( 'über' ), $channel ) );
		self::assertTrue( Filter::category( 'über' )( ( new Item( 'x' ) )->addCategory( 'ÜBER' ), $channel ) );
	}

	public function testSearchDoesNotJoinTitleAndDescription(): void
	{
		$filter	= Filter::search( 'ab' );
		self::assertFalse( $filter( new Item( 'a', NULL, 'b' ), new Channel() ) );
		self::assertTrue( Filter::search( 'a b' )( new Item( 'a', NULL, 'b' ), new Channel() ) );
		self::assertTrue( Filter::search( 'desc' )( new Item( NULL, NULL, 'desc' ), new Channel() ) );
	}

	public function testSince(): void
	{
		$channel	= new Channel();
		$filter		= Filter::since( new DateTimeImmutable( '2024-03-01 00:00:00+00:00' ) );
		self::assertTrue( $filter( ( new Item( 'x' ) )->setPubDate( new DateTimeImmutable( '2024-03-01 00:00:00+00:00' ) ), $channel ) );
		self::assertTrue( $filter( ( new Item( 'x' ) )->setPubDate( new DateTimeImmutable( '2024-03-01 00:30:00+00:30' ) ), $channel ) );
		self::assertFalse( $filter( ( new Item( 'x' ) )->setPubDate( new DateTimeImmutable( '2024-02-29 23:59:59+00:00' ) ), $channel ) );
		self::assertFalse( $filter( new Item( 'x' ), $channel ) );
	}

	public function testNot(): void
	{
		$channel	= new Channel( 'A' );
		$filter		= Filter::not( static fn( Item $item, Channel $channel ): bool => 'A' === $channel->getTitle() );
		self::assertFalse( $filter( new Item( 'x' ), $channel ) );
		self::assertTrue( $filter( new Item( 'x' ), new Channel( 'B' ) ) );
	}
}
