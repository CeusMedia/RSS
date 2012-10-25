<?php
/**
 *	Renders object structure to RSS.
 *	@copyright	2012 iamkriss.net
 *	@author		kriss <me@iamkriss.net> 
 *	@since		2012-08-20
 *	@version	1.0
 *	@license	LGPL 3
 */
class CMM_RSS_Renderer{

	static $version		= "2.0.11";

	static public function render( CMM_RSS_Channel $channel, $useTabs = FALSE ){
		$lines	= array();
		if( !strlen( $channel->getTitle() ) )
			throw new RuntimeException( 'Channel title cannot be empty' );
		if( !strlen( $channel->getLink() ) )
			throw new RuntimeException( 'Channel link cannot be empty' );
		if( !strlen( $channel->getDescription() ) )
			throw new RuntimeException( 'Channel description cannot be empty' );
		$lines[]	= self::renderNode( 'title', $channel->getTitle() );
		$lines[]	= self::renderNode( 'link', $channel->getLink() );
		$lines[]	= self::renderNode( 'description', $channel->getDescription() );

		if( strlen( $channel->getLanguage() ) )
			$lines[]	= self::renderNode( 'language', $channel->getLanguage() );
		if( strlen( $channel->getCopyright() ) )
			$lines[]	= self::renderNode( 'copyright', $channel->getCopyright() );
		
		if( ( $manager = $channel->getManager() ) ){
			$manager		= $manager[0].( !empty( $manager[1] ) ? ' ('.$manager[1].')' : '' );
			$lines[]	= self::renderNode( 'managingEditor', $manager );
		}
		if( ( $admin = $channel->getAdmin() ) ){
			$admin		= $admin[0].( !empty( $admin[1] ) ? ' ('.$admin[1].')' : '' );
			$lines[]	= self::renderNode( 'webMaster', $admin );
		}
		if( strlen( ( $date = $channel->getDate() ) ) ){
			if( !preg_match( "/^[0-9]+$/", $date ) && !strtotime( $date ) )
				throw new RuntimeException( 'Invalid item date: '.$date );
			$date		= preg_match( "/^[0-9]+$/", $date ) ? $date : strtotime( $date );
			$lines[]	= self::renderNode( 'pubDate', date( 'r', $date ) );
		}
		$lines[]	= self::renderNode( 'lastBuildDate', date( 'r', time() ) );

		if( strlen( $channel->getCategory() ) )
			$lines[]	= self::renderNode( 'category', $channel->getCategory() );
		if( strlen( $channel->getGenerator() ) )
			$lines[]	= self::renderNode( 'generator', $channel->getGenerator() );
		$lines[]	= self::renderNode( 'docs', "http://www.rssboard.org/rss-specification" );

		if( $channel->getCloud() )
			$lines[]	= self::renderNode( 'cloud', NULL, $channel->getCloud() ); 
		
		if( ( $image = $channel->getImage() ) ){
			$url		= self::renderNode( 'url', $image[0] );
			$title		= self::renderNode( 'title', $image[1] );
			$link		= self::renderNode( 'link', $image[2] );
			$desc		= empty( $image[3] ) ? '' : self::renderNode( 'description', $image[3] );
			$width		= empty( $image[4] ) ? '' : self::renderNode( 'width', $image[4] );
			$height		= empty( $image[5] ) ? '' : self::renderNode( 'height', $image[5] );
			$content	= $url.$title.$link.$desc.$width.$height;
			$lines[]	= self::renderNode( 'image', $content, array(), FALSE );
		}
		foreach( $channel->getItems() as $item )
			$lines[]	= self::renderItem( $item );
		$xml	= '<rss version="'.self::$version.'"><channel>'.join( $lines ).'</channel></rss>';
		return self::formatXml( $xml, $useTabs );
	}

	static protected function formatXml( $xml, $useTabs	= FALSE ){
		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= FALSE;
		$document->loadXml( $xml );
		$document->formatOutput = TRUE;
		if( !$useTabs )
			return $document->saveXml();
		$lines	= explode( "\n", $document->saveXml() );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^\t*  /", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^(\t*)  /", "\\1\t", $lines[$nr] );
		return implode( "\n", $lines );
	}
	
	static protected function renderNode( $name, $value, $attributes = array(), $encode = TRUE ){
		$list	= array();
		foreach( $attributes as $key => $attrValue )
			if( $attrValue !== NULL )
				$list[]	= ' '.$key.'="'.$attrValue.'"';
		if( $encode )
			$value	= htmlspecialchars( (string) $value );
		return '<'.$name.join( $list ).'>'.$value.'</'.$name.'>';
	}
	
	static protected function renderItem( CMM_RSS_Item $item ){
		$lines	= array();
		if( !strlen( $item->getTitle() ) )
			throw new RuntimeException( 'Item title cannot be empty' );
		$lines[]	= self::renderNode( 'title', $item->getTitle() );
		if( strlen( $item->getLink() ) )
			$lines[]	= self::renderNode( 'link', $item->getLink() );
		if( strlen( $item->getContent() ) )
			$lines[]	= self::renderNode( 'description', $item->getContent() );
		if( ( $author = $item->getAuthor() ) ){
			$author		= $author[0].( !empty( $author[1] ) ? ' ('.$author[1].')' : '' );
			$lines[]	= self::renderNode( 'author', $author );
		}
		if( strlen( $item->getCategory() ) )
			$lines[]	= self::renderNode( 'category', $item->getCategory() );
		if( strlen( ( $date = $item->getDate() ) ) ){
			if( !preg_match( "/^[0-9]+$/", $date ) && !strtotime( $date ) )
				throw new RuntimeException( 'Invalid item date: '.$date );
			$date		= preg_match( "/^[0-9]+$/", $date ) ? $date : strtotime( $date );
			$lines[]	= self::renderNode( 'pubDate', date( 'r', $date ) );
		}
		if( ( $guid = $item->getId() ) ){
			$attributes	= array( 'isPermaLink' => $guid[1] ? 'true' : NULL );
			$lines[]	= self::renderNode( 'guid', $guid[0], $attributes );
		}
		if( ( $source = $item->getSource() ) )
			$lines[]	= self::renderNode( 'source', $source[1], array( 'url' => $source[0] ) );
		return self::renderNode( 'item', join( $lines ), array(), FALSE );
	}
}
?>