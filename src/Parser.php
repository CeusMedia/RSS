<?php
class CMM_RSS_Parser{

	static public function parse( $xml ){
		$xml		= new XML_Element( $xml );
		$channel	= new CMM_RSS_Channel();
		
		foreach( $xml->channel->children() as $node ){
			if( $node->getName() == 'title' )
				$channel->setTitle( $node->getValue() );
			if( $node->getName() == 'link' )
				$channel->setLink( $node->getValue() );
			if( $node->getName() == 'description' )
				$channel->setDescription( $node->getValue() );
			if( $node->getName() == 'language' )
				$channel->setLanguage( $node->getValue() );
			if( $node->getName() == 'copyright' )
				$channel->setCopyright( $node->getValue() );
			if( $node->getName() == 'managingEditor' ){
				$user	= self::parseUser( $node );
				$channel->setManager( $user[0], $user[1] );
			}
			if( $node->getName() == 'webMaster' ){
				$user	= self::parseUser( $node );
				$channel->setAdmin( $user[0], $user[1] );
			}
			if( $node->getName() == 'pubDate' )
				$channel->setDate( strtotime( $node->getValue() ) );			
			if( $node->getName() == 'category' )
				$channel->setCategory( $node->getValue() );
			if( $node->getName() == 'generator' )
				$channel->setGenerator( $node->getValue() );
			if( $node->getName() == 'docs' )
				$channel->setDocs( $node->getValue() );
			if( $node->getName() == 'cloud' )
				$channel->setCloud( $node->getAttributes() );
			if( $node->getName() == 'image' ){
				$url	= $node->url->getValue() ? $node->url->getValue() : NULL;
				$title	= $node->title->getValue() ? $node->title->getValue() : NULL;
				$link	= $node->link->getValue() ? $node->link->getValue() : NULL;
				$desc	= $node->description->getValue() ? $node->description->getValue() : NULL;
				$width	= $node->width->getValue() ? $node->width->getValue() : NULL;
				$height	= $node->height->getValue() ? $node->height->getValue() : NULL;
				$channel->setImage( $url, $title, $link, $desc, $width, $height );
			}
		}
		foreach( $xml->channel->item as $item )
			$channel->addItem( self::parseItem( $item ) );
		return $channel;
	}

	static protected function parseItem( XML_Element $xml ){
		$item	= new CMM_RSS_Item();
		foreach( $xml->children() as $node ){
			if( $node->getName() == 'title' )
				$item->setTitle( $node->getValue() );
			if( $node->getName() == 'link' )
				$item->setLink( $node->getValue() );
			if( $node->getName() == 'description' )
				$item->setContent( trim( $node->getValue() ) );
			if( $node->getName() == 'author' ){
				$user	= self::parseUser( $node->getValue() );
				$item->setAuthor( $user[0], $user[1] );
			}
			if( $node->getName() == 'category' )
				$item->setCategory( $node->getValue() );
			if( $node->getName() == 'guid' ){
				$isPermaLink	= NULL;
				if( $node->hasAttribute( 'isPermaLink' ) && $node->getAttribute( 'isPermaLink' ) )
					$isPermaLink	= TRUE;
				$item->setId( $node->getValue(), $isPermaLink );
			}
			if( $node->getName() == 'pubDate' )
				$item->setDate( strtotime( $node->getValue() ) );
			if( $node->getName() == 'source' ){
				$url	= NULL;
				if( $node->hasAttribute( 'url' ) && $node->getAttribute( 'url' ) )
					$url	= TRUE;
				$item->setSource( $node->getValue(), $url );
			}
		}
		return $item;
	}

	static protected function parseUser( $xml ){
		$parts	= explode( " (", $xml->getValue() );
		$name	= array_shift( $parts );
		$email	= count( $parts ) ? substr( join( $parts ), 0, -1 ) : NULL;
		return array( $name, $email );
	}
}
?>