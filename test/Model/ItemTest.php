<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Model;

use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Model\Source;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Item::class )]
#[UsesClass( Category::class )]
#[UsesClass( Enclosure::class )]
#[UsesClass( Guid::class )]
#[UsesClass( Person::class )]
#[UsesClass( Source::class )]
class ItemTest extends TestCase
{
	public function testDefaults(): void
	{
		$item	= new Item();
		self::assertNull( $item->getTitle() );
		self::assertNull( $item->getLink() );
		self::assertNull( $item->getDescription() );
		self::assertNull( $item->getAuthor() );
		self::assertSame( [], $item->getCategories() );
		self::assertNull( $item->getComments() );
		self::assertNull( $item->getEnclosure() );
		self::assertNull( $item->getGuid() );
		self::assertNull( $item->getPubDate() );
		self::assertNull( $item->getSource() );
	}

	public function testConstructor(): void
	{
		$item	= new Item( 'Title', 'https://example.org/1', 'Description' );
		self::assertSame( 'Title', $item->getTitle() );
		self::assertSame( 'https://example.org/1', $item->getLink() );
		self::assertSame( 'Description', $item->getDescription() );
	}

	public function testSettersAndGetters(): void
	{
		$author		= new Person( 'a@example.org', 'Anna' );
		$enclosure	= new Enclosure( 'https://example.org/a.mp3', 100, 'audio/mpeg' );
		$guid		= new Guid( 'https://example.org/1' );
		$source		= new Source( 'Other', 'https://other.example.org/rss' );
		$item		= ( new Item() )
			->setTitle( 'Title' )
			->setLink( 'https://example.org/1' )
			->setDescription( '<p>Content</p>' )
			->setAuthor( $author )
			->setComments( 'https://example.org/1#comments' )
			->setEnclosure( $enclosure )
			->setGuid( $guid )
			->setSource( $source );

		self::assertSame( 'Title', $item->getTitle() );
		self::assertSame( 'https://example.org/1', $item->getLink() );
		self::assertSame( '<p>Content</p>', $item->getDescription() );
		self::assertSame( $author, $item->getAuthor() );
		self::assertSame( 'https://example.org/1#comments', $item->getComments() );
		self::assertSame( $enclosure, $item->getEnclosure() );
		self::assertSame( $guid, $item->getGuid() );
		self::assertSame( $source, $item->getSource() );
	}

	public function testCategories(): void
	{
		$category	= new Category( 'B', 'domain' );
		$item		= ( new Item() )->addCategory( 'A' )->addCategory( $category );
		self::assertCount( 2, $item->getCategories() );
		self::assertSame( 'A', $item->getCategories()[0]->name );
		self::assertSame( $category, $item->getCategories()[1] );

		$item->setCategories( [new Category( 'C' )] );
		self::assertSame( ['C'], array_map( strval( ... ), $item->getCategories() ) );
	}

	public function testPubDateIsConvertedToImmutable(): void
	{
		$date	= new DateTime( '2024-03-07 09:05:00+01:00' );
		$item	= ( new Item() )->setPubDate( $date );
		$date->modify( '+1 day' );
		self::assertInstanceOf( DateTimeImmutable::class, $item->getPubDate() );
		self::assertSame( '2024-03-07T09:05:00+01:00', $item->getPubDate()->format( DATE_ATOM ) );
		self::assertNull( $item->setPubDate( NULL )->getPubDate() );
	}
}
