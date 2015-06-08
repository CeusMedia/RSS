<?php
/**
 *	...
 *
 *	Copyright (c) 2012-2015 Christian Würker / {@link http://ceusmedia.de/ Ceus Media}
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *	@category		Library
 *	@package		CeusMedia_Rss
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Rss
 */
namespace CeusMedia\Rss;
/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia_Rss
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Rss
 */
class Parser{

	static public function parse( $xml ){
		$xml		= new \XML_Element( $xml );
		$channel	= new \CeusMedia\Rss\Model\Channel();

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

	static protected function parseItem( \XML_Element $xml ){
		$item	= new \CeusMedia\Rss\Model\Item();
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
