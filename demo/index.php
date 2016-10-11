<?php
(@include '../vendor/autoload.php') or die('Please use composer to install required packages.');

ob_start();
try{
	$channel1	= new \CeusMedia\RSS\Model\Channel();
	$channel1->setTitle( "RSS Test Channel 1");
	$channel1->setLink( "http://example.com/#channel1");
	$channel1->setDescription( "...");

	$item1	= new \CeusMedia\RSS\Model\Item();
	$item1->setTitle( "Item 1-1" );
	$item1->setDate( "2015-01-01" );
	$item1->setLink( "http://example.com/#channel1_item1");

	$channel1->addItem( $item1 );

	$rss1xml		= \CeusMedia\RSS\Renderer::render( $channel1 );

	print '<h3>Generating</h3>';
	print '<xmp>'.$rss1xml.'</xmp>';

	print '<h3>Reading</h3>';
	$channel2	= \CeusMedia\RSS\Parser::parse( $rss1xml );
	print 'Channel: '.$channel2->getTitle().'<br/>';
	print '- Link: '.$channel2->getLink().'<br/>';
	print '- Desc: '.$channel2->getDescription().'<br/>';
	foreach( $channel2->getItems() as $nr => $item ){
		print '- Item #'.( $nr + 1 ).'<br/>';
		print '... Title: '.$item->getTitle().'<br/>';
		print '... Link: '.$item->getLink().'<br/>';
	}


	$channel2	= new \CeusMedia\RSS\Model\Channel();
	$channel2->setTitle( "RSS Test Channel 2");
	$channel2->setLink( "http://example.com/#channel2");
	$channel2->setDescription( "...");

	$item1	= new \CeusMedia\RSS\Model\Item();
	$item1->setTitle( "Item 2-1" );
	$item1->setDate( "2015-01-02" );
	$item1->setLink( "http://example.com/#channel2_item1");

	$channel2->addItem( $item1 );

	$combiner	= new \CeusMedia\RSS\Combiner();
	$combiner->addChannel( $channel1 );
	$combiner->addChannel( $channel2 );

	$channel3	= new \CeusMedia\RSS\Model\Channel();
	$channel3->setTitle( "RSS Test Channel 3");
	$channel3->setLink( "http://example.com/#channel3");
	$channel3->setDescription( "...");
	$rss3xml	= $combiner->render( $channel3 );
	print '<h3>Combining</h3>';
	print '<xmp>'.$rss3xml.'</xmp>';
}
catch( Exception $e ){
	UI_HTML_Exception_Page::display( $e );
}


$body	= '
<div class="container">
	<h1 class="muted">CeusMedia Component Demo</h1>
	<h2>RSS</h2>
	'.ob_get_clean().'
</div>';
$page	= new UI_HTML_PageFrame();
$page->addStylesheet( "http://cdn.int1a.net/css/bootstrap.min.css" );
$page->addBody( $body );
print $page->build();
