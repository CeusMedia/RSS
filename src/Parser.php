<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
 *
 *	Copyright (c) 2012-2023 Christian Würker / {@link https://ceusmedia.de/ Ceus Media}
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
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2023 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
namespace CeusMedia\RSS;

use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2023 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Parser
{
	public static function parse( string $xml ): Channel
	{
		$xml		= new XmlElement( $xml );
		$channel	= new Channel();

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
				$channel->setDate( strtotime( $node->getValue() ) ?: NULL );
			if( $node->getName() == 'category' )
				$channel->setCategory( $node->getValue() );
			if( $node->getName() == 'generator' )
				$channel->setGenerator( $node->getValue() );
			if( $node->getName() == 'docs' )
				$channel->setDocs( $node->getValue() );
			if( $node->getName() == 'cloud' )
				$channel->setCloud( $node->getAttributes() );
			if( $node->getName() == 'image' ){
				$image	= new Image();
				$image->setUrl( $node->url->getValue() );
				$image->setTitle( $node->title->getValue() );
				$image->setLink( $node->link->getValue() );
				$image->setDescription( $node->description->getValue() ?: NULL );
				$image->setWidth( (int) $node->width->getValue() ?: NULL );
				$image->setHeight( (int) $node->height->getValue() ?: NULL );
				$channel->setImage( $image );
			}
		}
		foreach( $xml->channel->item as $item )
			$channel->addItem( self::parseItem( $item ) );
		return $channel;
	}

	protected static function parseItem( XmlElement $xml ): Item
	{
		$item	= new Item();
		foreach( $xml->children() as $node ){
			switch( $node->getName() ){
				case 'title':
					$item->setTitle( $node->getValue() );
					break;
				case 'link':
					$item->setLink( $node->getValue() );
					break;
				case 'description':
					$item->setContent( trim( $node->getValue() ) );
					break;
				case 'author':
					$user	= self::parseUser( $node );
					$item->setAuthor( $user[0], $user[1] );
					break;
				case 'category':
					$item->setCategory( $node->getValue() );
					break;
				case 'guid':
					$isPermaLink	= $node->hasAttribute( 'isPermaLink' ) &&
						'true'	=== $node->getAttribute( 'isPermaLink' );
					$item->setId( $node->getValue(), $isPermaLink );
					break;
				case 'pubDate':
					$item->setDate( strtotime( $node->getValue() ) ?: NULL );
					break;
				case 'source':
					$url	= $node->hasAttribute( 'url' ) ? $node->getAttribute( 'url' ) : NULL;
					$item->setSource( $node->getValue(), $url );
					break;
			}
		}
		return $item;
	}

	protected static function parseUser( XmlElement $xml ): array
	{
		$parts	= explode( " (", $xml->getValue() );
		$name	= array_shift( $parts );
		$email	= count( $parts ) ? substr( join( $parts ), 0, -1 ) : NULL;
		return array( $name, $email );
	}
}
