<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Model;

use CeusMedia\RSS\Model\Image;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass( Image::class )]
class ImageTest extends TestCase
{
	public function testConstructor(): void
	{
		$image	= new Image( 'https://example.org/logo.png' );
		self::assertSame( 'https://example.org/logo.png', $image->getUrl() );
		self::assertNull( $image->getTitle() );
		self::assertNull( $image->getLink() );
		self::assertNull( $image->getDescription() );
		self::assertNull( $image->getWidth() );
		self::assertNull( $image->getHeight() );

		$image	= new Image( 'u', 'Title', 'https://example.org/' );
		self::assertSame( 'Title', $image->getTitle() );
		self::assertSame( 'https://example.org/', $image->getLink() );
	}

	public function testSettersAndGetters(): void
	{
		$image	= ( new Image( 'u' ) )
			->setUrl( 'https://example.org/logo.png' )
			->setTitle( 'Logo' )
			->setLink( 'https://example.org/' )
			->setDescription( 'Description' )
			->setWidth( Image::MAX_WIDTH )
			->setHeight( Image::MAX_HEIGHT );
		self::assertSame( 'https://example.org/logo.png', $image->getUrl() );
		self::assertSame( 'Logo', $image->getTitle() );
		self::assertSame( 'https://example.org/', $image->getLink() );
		self::assertSame( 'Description', $image->getDescription() );
		self::assertSame( 144, $image->getWidth() );
		self::assertSame( 400, $image->getHeight() );
		self::assertNull( $image->setWidth( NULL )->getWidth() );
	}

	public function testMinimumSize(): void
	{
		$image	= ( new Image( 'u' ) )->setWidth( 1 )->setHeight( 1 );
		self::assertSame( 1, $image->getWidth() );
		self::assertSame( 1, $image->getHeight() );
	}

	/**
	 *	@return		array<string, array{callable(): mixed, string}>
	 */
	public static function invalidProvider(): array
	{
		return [
			'empty url'			=> [static fn() => new Image( '' ), 'Image URL cannot be empty'],
			'set empty url'		=> [static fn() => ( new Image( 'u' ) )->setUrl( ' ' ), 'Image URL cannot be empty'],
			'width 0'			=> [static fn() => ( new Image( 'u' ) )->setWidth( 0 ), 'Image width must be between 1 and 144'],
			'width 145'			=> [static fn() => ( new Image( 'u' ) )->setWidth( 145 ), 'Image width must be between 1 and 144'],
			'height 0'			=> [static fn() => ( new Image( 'u' ) )->setHeight( 0 ), 'Image height must be between 1 and 400'],
			'height 401'		=> [static fn() => ( new Image( 'u' ) )->setHeight( 401 ), 'Image height must be between 1 and 400'],
		];
	}

	/**
	 *	@param		callable(): mixed		$factory
	 */
	#[DataProvider( 'invalidProvider' )]
	public function testInvalidValuesThrowException( callable $factory, string $message ): void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( $message );
		$factory();
	}
}
