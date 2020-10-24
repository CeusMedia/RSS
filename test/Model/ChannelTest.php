<?php
use PHPUnit\Framework\TestCase;
use CeusMedia\RSS\Model\Channel;

/**
 *	@covers	CeusMedia\RSS\Model\Channel
 */
class Test_Model_ChannelTest extends TestCase
{
	public function setUp(): void
	{
		$this->model	= new Channel();

	}

	public function testGetAdmin()
	{
		$model	= new Channel();
		$model->setAdmin( 'name', 'email' );


		$this->assertEquals( array( 'name', 'email' ), $model->getAdmin() );

	}
}
