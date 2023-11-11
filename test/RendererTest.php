<?php
namespace CeusMedia\RSSTest;

use PHPUnit\Framework\TestCase;
use CeusMedia\RSS\Renderer;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Image;

/**
 *	@covers	CeusMedia\RSS\Renderer
 */
class RendererTest extends TestCase
{
	public function setUp(): void
	{
	}

	public function testRender()
	{
		$channel	= new Channel();
		$channel->setTitle( 'Test 1' );
		$channel->setDescription( 'Description of channel' );
		$channel->setLink( 'https://ceusmedia.de/' );
		$channel->setImage( (new Image())
			->setUrl( 'https://example.org/image1' )
			->setTitle( 'Example Image 1' )
		);

		$channel->addItem( (new Item())
			->setCategory( 'Test' )
			->setTitle( 'Test 1-1' )
			->setLink( 'https://example.org/test1/test1-1' )
		);

		$xml	= Renderer::render( $channel );
		$date	= date( 'r', time() );
//		file_put_contents( __DIR__.'/test1.rss', $xml );

//		$xml	= preg_replace( '/(<lastBuildDate>).+(<\/lastBuildDate>)/', "\\1$date\\2", $xml );

		$expected	= file_get_contents( __DIR__.'/test1.rss' );
		$expected	= preg_replace( '/(<lastBuildDate>).+(<\/lastBuildDate>)/', "\\1$date\\2", $expected );
//		$this->assertEquals( Channel::class, getClass( $channel ) );
		$this->assertEquals( $expected, $xml );
	}
}
