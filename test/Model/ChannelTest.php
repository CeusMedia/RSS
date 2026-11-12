<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Model;

use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Cloud;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Model\TextInput;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( Channel::class )]
#[CoversClass( \CeusMedia\RSS\Model\ExtensionDataTrait::class )]
#[UsesClass( Category::class )]
#[UsesClass( Cloud::class )]
#[UsesClass( Image::class )]
#[UsesClass( Item::class )]
#[UsesClass( Person::class )]
#[UsesClass( TextInput::class )]
class ChannelTest extends TestCase
{
	public function testDefaults(): void
	{
		$channel	= new Channel();
		self::assertSame( '', $channel->getTitle() );
		self::assertSame( '', $channel->getLink() );
		self::assertSame( '', $channel->getDescription() );
		self::assertSame( Channel::DEFAULT_DOCS, $channel->getDocs() );
		self::assertSame( [], $channel->getCategories() );
		self::assertSame( [], $channel->getItems() );
		self::assertSame( [], $channel->getSkipDays() );
		self::assertSame( [], $channel->getSkipHours() );
		self::assertNull( $channel->getCloud() );
		self::assertNull( $channel->getImage() );
		self::assertNull( $channel->getLastBuildDate() );
		self::assertNull( $channel->getManagingEditor() );
		self::assertNull( $channel->getPubDate() );
		self::assertNull( $channel->getRating() );
		self::assertNull( $channel->getTextInput() );
		self::assertNull( $channel->getTtl() );
		self::assertNull( $channel->getWebMaster() );
	}

	public function testConstructor(): void
	{
		$channel	= new Channel( 'Title', 'https://example.org/', 'Description' );
		self::assertSame( 'Title', $channel->getTitle() );
		self::assertSame( 'https://example.org/', $channel->getLink() );
		self::assertSame( 'Description', $channel->getDescription() );
	}

	public function testSettersAndGetters(): void
	{
		$cloud		= new Cloud( 'rpc.example.org', 80, '/RPC2', 'ping', 'soap' );
		$editor		= new Person( 'editor@example.org' );
		$image		= new Image( 'https://example.org/logo.png' );
		$textInput	= new TextInput( 'Go', 'Search', 'q', 'https://example.org/search' );
		$webMaster	= new Person( 'admin@example.org', 'Admin' );
		$channel	= ( new Channel() )
			->setTitle( 'Title' )
			->setLink( 'https://example.org/' )
			->setDescription( 'Description' )
			->setLanguage( 'de-de' )
			->setCopyright( '(c) Example' )
			->setManagingEditor( $editor )
			->setWebMaster( $webMaster )
			->setGenerator( 'Generator' )
			->setDocs( NULL )
			->setCloud( $cloud )
			->setTtl( 60 )
			->setImage( $image )
			->setRating( 'PICS' )
			->setTextInput( $textInput );

		self::assertSame( 'Title', $channel->getTitle() );
		self::assertSame( 'https://example.org/', $channel->getLink() );
		self::assertSame( 'Description', $channel->getDescription() );
		self::assertSame( 'de-de', $channel->getLanguage() );
		self::assertSame( '(c) Example', $channel->getCopyright() );
		self::assertSame( $editor, $channel->getManagingEditor() );
		self::assertSame( $webMaster, $channel->getWebMaster() );
		self::assertSame( 'Generator', $channel->getGenerator() );
		self::assertNull( $channel->getDocs() );
		self::assertSame( $cloud, $channel->getCloud() );
		self::assertSame( 60, $channel->getTtl() );
		self::assertSame( $image, $channel->getImage() );
		self::assertSame( 'PICS', $channel->getRating() );
		self::assertSame( $textInput, $channel->getTextInput() );
	}

	public function testDatesAreConvertedToImmutable(): void
	{
		$date		= new DateTime( '2024-01-01 12:00:00+00:00' );
		$channel	= ( new Channel() )->setPubDate( $date )->setLastBuildDate( $date );
		$date->modify( '+1 day' );
		self::assertInstanceOf( DateTimeImmutable::class, $channel->getPubDate() );
		self::assertSame( '2024-01-01', $channel->getPubDate()->format( 'Y-m-d' ) );
		self::assertSame( '2024-01-01', $channel->getLastBuildDate()?->format( 'Y-m-d' ) );
	}

	public function testCategories(): void
	{
		$channel	= ( new Channel() )->addCategory( 'A' )->addCategory( new Category( 'B', 'd' ) );
		self::assertSame( ['A', 'B'], array_map( strval( ... ), $channel->getCategories() ) );
		$category	= new Category( 'C' );
		self::assertSame( [$category], $channel->setCategories( [$category] )->getCategories() );
		self::assertSame( [], $channel->setCategories( [] )->getCategories() );
	}

	public function testItems(): void
	{
		$item1		= new Item( '1' );
		$item2		= new Item( '2' );
		$channel	= ( new Channel() )->addItem( $item1 )->addItem( $item2 );
		self::assertSame( [$item1, $item2], $channel->getItems() );
		self::assertSame( [$item2], $channel->setItems( [$item2] )->getItems() );
	}

	public function testExtensionData(): void
	{
		$channel	= new Channel();
		$data		= new \ArrayObject( ['a' => 1] );
		self::assertNull( $channel->getExtension( \ArrayObject::class ) );
		self::assertFalse( $channel->hasExtension( \ArrayObject::class ) );
		self::assertSame( $channel, $channel->setExtension( $data ) );
		self::assertTrue( $channel->hasExtension( \ArrayObject::class ) );
		self::assertSame( $data, $channel->getExtension( \ArrayObject::class ) );
		self::assertSame( [\ArrayObject::class => $data], $channel->getExtensions() );

		$replacement	= new \ArrayObject( ['b' => 2] );
		self::assertSame( $replacement, $channel->setExtension( $replacement )->getExtension( \ArrayObject::class ) );
		self::assertSame( [], $channel->removeExtension( \ArrayObject::class )->getExtensions() );
	}

	public function testSkipHoursAreUniqueAndSorted(): void
	{
		self::assertSame( [0, 5, 23], ( new Channel() )->setSkipHours( [23, 5, 0, 5] )->getSkipHours() );
	}

	public function testSkipDaysAreNormalizedAndSorted(): void
	{
		$channel	= ( new Channel() )->setSkipDays( ['sunday', 'Saturday', 'MONDAY', 'Sunday'] );
		self::assertSame( ['Monday', 'Saturday', 'Sunday'], $channel->getSkipDays() );
	}

	public function testInvalidSkipHourThrowsException(): void
	{
		$this->expectException( InvalidArgumentException::class );
		( new Channel() )->setSkipHours( [24] );
	}

	public function testInvalidSkipDayThrowsException(): void
	{
		$this->expectException( InvalidArgumentException::class );
		( new Channel() )->setSkipDays( ['Caturday'] );
	}

	public function testTtlZeroIsAllowed(): void
	{
		self::assertSame( 0, ( new Channel() )->setTtl( 0 )->getTtl() );
	}

	public function testSkipValuesAreTrimmedAndListed(): void
	{
		$channel	= ( new Channel() )->setSkipDays( [' sunday '] )->setSkipHours( [3, 1, 3] );
		self::assertSame( ['Sunday'], $channel->getSkipDays() );
		self::assertSame( [0 => 1, 1 => 3], $channel->getSkipHours() );
	}

	public function testInvalidSkipValueMessages(): void
	{
		try{
			( new Channel() )->setSkipDays( ['Caturday'] );
			self::fail( 'Expected exception' );
		}
		catch( InvalidArgumentException $e ){
			self::assertSame( 'Invalid skip day: Caturday', $e->getMessage() );
		}
		try{
			( new Channel() )->setSkipHours( [-1] );
			self::fail( 'Expected exception' );
		}
		catch( InvalidArgumentException $e ){
			self::assertSame( 'Skip hour must be between 0 and 23, -1 given', $e->getMessage() );
		}
	}

	public function testNegativeTtlThrowsException(): void
	{
		$this->expectException( InvalidArgumentException::class );
		( new Channel() )->setTtl( -1 );
	}
}
