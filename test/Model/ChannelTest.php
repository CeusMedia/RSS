<?php
class Test_Model_ChannelTest extends PHPUnit_Framework_TestCase{

	public function setUp(){
		$this->model	= new \CeusMedia\RSS\Model\Channel();

	}

	public function testGetAdmin(){
		$model	= new \CeusMedia\RSS\Model\Channel();
		$model->setAdmin( 'name', 'email' );


		$this->assertEquals( array( 'name', 'email' ), $model->getAdmin() );

	}
}
?>
