<?php
use PHPUnit\Framework\TestCase;
use CeusMedia\RSS\Parser;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;

/**
 *	@covers	CeusMedia\RSS\Parser
 */
class Test_ChannelTest extends TestCase
{
	public function setUp(): void
	{

	}

	public function testParse()
	{
		$xml		= file_get_contents( __DIR__.'/test1.rss' );
		$channel	= Parser::parse( $xml );

		$this->assertEquals( Channel::class, get_class( $channel ) );
		$this->assertEquals( 'Test 1', $channel->getTitle() );
	}
}
