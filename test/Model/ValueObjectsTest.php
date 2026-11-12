<?php
declare( strict_types=1 );

namespace CeusMedia\RSSTest\Model;

use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Cloud;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Model\Source;
use CeusMedia\RSS\Model\TextInput;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass( Category::class )]
#[CoversClass( Cloud::class )]
#[CoversClass( Enclosure::class )]
#[CoversClass( Guid::class )]
#[CoversClass( Person::class )]
#[CoversClass( Source::class )]
#[CoversClass( TextInput::class )]
class ValueObjectsTest extends TestCase
{
	/**
	 *	@return		array<string, array{string, string, ?string}>
	 */
	public static function personStringProvider(): array
	{
		return [
			'email only'			=> ['a@example.org', 'a@example.org', NULL],
			'email and name'		=> ['a@example.org (Anna)', 'a@example.org', 'Anna'],
			'name with parentheses'	=> ['a@example.org (Anna (Admin))', 'a@example.org', 'Anna (Admin)'],
			'surrounding spaces'	=> ['  a@example.org   (Anna)  ', 'a@example.org', 'Anna'],
			'empty name'			=> ['a@example.org ()', 'a@example.org', NULL],
			'name only (invalid)'	=> ['Anna Author', 'Anna Author', NULL],
		];
	}

	#[DataProvider( 'personStringProvider' )]
	public function testPersonFromString( string $value, string $email, ?string $name ): void
	{
		$person	= Person::fromString( $value );
		self::assertSame( $email, $person->email );
		self::assertSame( $name, $person->name );
	}

	public function testPersonToString(): void
	{
		self::assertSame( 'a@example.org (Anna)', (string) new Person( 'a@example.org', 'Anna' ) );
		self::assertSame( 'a@example.org', (string) new Person( 'a@example.org' ) );
		self::assertSame( 'a@example.org', (string) new Person( 'a@example.org', ' ' ) );
	}

	public function testGuidIsPermaLinkByDefault(): void
	{
		$guid	= new Guid( 'https://example.org/1' );
		self::assertTrue( $guid->isPermaLink );
		self::assertSame( 'https://example.org/1', (string) $guid );
		self::assertFalse( ( new Guid( 'urn:x', FALSE ) )->isPermaLink );
	}

	public function testCategory(): void
	{
		$category	= new Category( 'Sports/Football', 'https://example.org/tax' );
		self::assertSame( 'Sports/Football', $category->name );
		self::assertSame( 'https://example.org/tax', $category->domain );
		self::assertSame( 'Sports/Football', (string) $category );
		self::assertNull( ( new Category( 'x' ) )->domain );
	}

	public function testEnclosureDefaults(): void
	{
		$enclosure	= new Enclosure( 'https://example.org/a.mp3' );
		self::assertSame( 0, $enclosure->length );
		self::assertSame( 'application/octet-stream', $enclosure->type );
	}

	public function testCloud(): void
	{
		$cloud	= new Cloud( 'rpc.example.org', 80, '/RPC2', 'ping', 'xml-rpc' );
		self::assertSame( 'rpc.example.org', $cloud->domain );
		self::assertSame( 80, $cloud->port );
		self::assertSame( '/RPC2', $cloud->path );
		self::assertSame( 'ping', $cloud->registerProcedure );
		self::assertSame( 'xml-rpc', $cloud->protocol );
	}

	public function testSourceAndTextInput(): void
	{
		$source	= new Source( 'Other', 'https://other.example.org/rss' );
		self::assertSame( 'Other', $source->title );
		self::assertSame( 'https://other.example.org/rss', $source->url );
		$textInput	= new TextInput( 'Go', 'Search', 'q', 'https://example.org/search' );
		self::assertSame( 'q', $textInput->name );
	}

	/**
	 *	@return		array<string, array{callable(): object, string}>
	 */
	public static function invalidValueProvider(): array
	{
		return [
			'person w/o email'		=> [static fn() => new Person( ' ' ), 'Email of person cannot be empty'],
			'person from empty'		=> [static fn() => Person::fromString( '' ), 'Email of person cannot be empty'],
			'empty guid'			=> [static fn() => new Guid( '' ), 'GUID cannot be empty'],
			'empty category'		=> [static fn() => new Category( '' ), 'Category name cannot be empty'],
			'source w/o url'		=> [static fn() => new Source( 'S', '' ), 'Source URL cannot be empty'],
			'enclosure w/o url'		=> [static fn() => new Enclosure( '' ), 'Enclosure URL cannot be empty'],
			'enclosure negative'	=> [static fn() => new Enclosure( 'u', -1 ), 'Enclosure length cannot be negative'],
			'enclosure w/o type'	=> [static fn() => new Enclosure( 'u', 1, '' ), 'Enclosure type cannot be empty'],
			'cloud w/o domain'		=> [static fn() => new Cloud( '', 80, '/', 'p', 'soap' ), 'Cloud domain cannot be empty'],
			'cloud port 0'			=> [static fn() => new Cloud( 'd', 0, '/', 'p', 'soap' ), 'Cloud port must be between'],
			'cloud port 65536'		=> [static fn() => new Cloud( 'd', 65536, '/', 'p', 'soap' ), 'Cloud port must be between'],
			'cloud w/o protocol'	=> [static fn() => new Cloud( 'd', 80, '/', 'p', '' ), 'Cloud protocol cannot be empty'],
			'text input w/o name'	=> [static fn() => new TextInput( 't', 'd', '', 'l' ), 'Text input name cannot be empty'],
		];
	}

	/**
	 *	@return		array<string, array{callable(): object}>
	 */
	public static function whitespaceValueProvider(): array
	{
		return [
			'guid'					=> [static fn() => new Guid( ' ' )],
			'category'				=> [static fn() => new Category( "\t" )],
			'source url'			=> [static fn() => new Source( 'S', ' ' )],
			'enclosure url'			=> [static fn() => new Enclosure( ' ' )],
			'enclosure type'		=> [static fn() => new Enclosure( 'u', 1, ' ' )],
			'cloud domain'			=> [static fn() => new Cloud( ' ', 80, '/', 'p', 'soap' )],
			'cloud protocol'		=> [static fn() => new Cloud( 'd', 80, '/', 'p', ' ' )],
			'text input title'		=> [static fn() => new TextInput( ' ', 'd', 'n', 'l' )],
			'text input value'		=> [static fn() => new TextInput( 't', ' ', 'n', 'l' )],
		];
	}

	/**
	 *	@param		callable(): object		$factory
	 */
	#[DataProvider( 'whitespaceValueProvider' )]
	public function testWhitespaceOnlyValuesAreInvalid( callable $factory ): void
	{
		$this->expectException( InvalidArgumentException::class );
		$factory();
	}

	public function testBoundaryValues(): void
	{
		self::assertSame( 1, ( new Cloud( 'd', 1, '/', 'p', 'soap' ) )->port );
		self::assertSame( 65535, ( new Cloud( 'd', 65535, '/', 'p', 'soap' ) )->port );
		self::assertSame( 0, ( new Enclosure( 'u', 0 ) )->length );
	}

	public function testPersonFromStringWithTextAfterName(): void
	{
		$person	= Person::fromString( 'a@example.org (Anna) trailing' );
		self::assertSame( 'a@example.org (Anna) trailing', $person->email );
		self::assertNull( $person->name );
		self::assertSame( "Anna\nAuthor", Person::fromString( "a@example.org (Anna\nAuthor)" )->name );
		self::assertSame( 'Anna', Person::fromString( 'a@example.org ( Anna )' )->name );
	}

	/**
	 *	@param		callable(): object		$factory
	 */
	#[DataProvider( 'invalidValueProvider' )]
	public function testInvalidValuesThrowException( callable $factory, string $message ): void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( $message );
		$factory();
	}
}
