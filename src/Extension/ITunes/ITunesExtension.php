<?php
declare( strict_types=1 );

/**
 *	Extension for podcast elements of Apple Podcasts (itunes:*).
 *
 *	Copyright (c) 2012-2026 Christian Würker / {@link https://ceusmedia.de/ Ceus Media}
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
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
namespace CeusMedia\RSS\Extension\ITunes;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use DOMElement;
use InvalidArgumentException;

/**
 *	Extension for podcast elements of Apple Podcasts (itunes:*).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class ITunesExtension extends AbstractExtension
{
	/**	Namespace URI of iTunes podcast elements */
	public const string NAMESPACE_URI	= 'http://www.itunes.com/dtds/podcast-1.0.dtd';

	/**
	 *	Returns namespace URI of extension elements.
	 *	@return		string
	 */
	public function getNamespace(): string
	{
		return self::NAMESPACE_URI;
	}

	/**
	 *	Returns preferred namespace prefix used on rendering.
	 *	@return		string
	 */
	public function getPrefix(): string
	{
		return 'itunes';
	}

	/**
	 *	Parses itunes:* element of channel into podcast data. Invalid values are skipped.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Channel		$channel		Channel model
	 */
	public function parseChannelElement( DOMElement $element, Channel $channel ): void
	{
		$data	= $channel->getExtension( ChannelData::class ) ?? new ChannelData();
		$value	= Dom::getText( $element );
		try{
			switch( $element->localName ){
				case 'author':
					$data->setAuthor( Dom::nullIfEmpty( $value ) );
					break;
				case 'block':
					$data->setBlock( 'yes' === strtolower( $value ) );
					break;
				case 'category':
					$category	= $this->parseCategory( $element );
					if( NULL !== $category )
						$data->addCategory( $category );
					break;
				case 'complete':
					$data->setComplete( 'yes' === strtolower( $value ) );
					break;
				case 'explicit':
					$data->setExplicit( self::parseExplicit( $value ) );
					break;
				case 'image':
					$data->setImage( Dom::nullIfEmpty( $element->getAttribute( 'href' ) ) );
					break;
				case 'new-feed-url':
					$data->setNewFeedUrl( Dom::nullIfEmpty( $value ) );
					break;
				case 'owner':
					$data->setOwner( $this->parseOwner( $element ) );
					break;
				case 'title':
					$data->setTitle( Dom::nullIfEmpty( $value ) );
					break;
				case 'type':
					$data->setType( Dom::nullIfEmpty( strtolower( $value ) ) );
					break;
				default:
					return;
			}
		}
		catch( InvalidArgumentException ){
			return;
		}
		$channel->setExtension( $data );
	}

	/**
	 *	Parses itunes:* element of item into episode data. Invalid values are skipped.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Item		$item		Item model
	 */
	public function parseItemElement( DOMElement $element, Item $item ): void
	{
		$data	= $item->getExtension( ItemData::class ) ?? new ItemData();
		$value	= Dom::getText( $element );
		try{
			switch( $element->localName ){
				case 'block':
					$data->setBlock( 'yes' === strtolower( $value ) );
					break;
				case 'duration':
					$data->setDuration( self::parseDuration( $value ) );
					break;
				case 'episode':
					$data->setEpisode( ctype_digit( $value ) ? (int) $value : NULL );
					break;
				case 'episodeType':
					$data->setEpisodeType( Dom::nullIfEmpty( strtolower( $value ) ) );
					break;
				case 'explicit':
					$data->setExplicit( self::parseExplicit( $value ) );
					break;
				case 'image':
					$data->setImage( Dom::nullIfEmpty( $element->getAttribute( 'href' ) ) );
					break;
				case 'season':
					$data->setSeason( ctype_digit( $value ) ? (int) $value : NULL );
					break;
				case 'title':
					$data->setTitle( Dom::nullIfEmpty( $value ) );
					break;
				default:
					return;
			}
		}
		catch( InvalidArgumentException ){
			return;
		}
		$item->setExtension( $data );
	}

	/**
	 *	Parses duration given as seconds, "MM:SS" or "HH:MM:SS".
	 *	@param		string		$value		Value to parse
	 *	@return		?int		Seconds, NULL if invalid
	 */
	public static function parseDuration( string $value ): ?int
	{
		if( ctype_digit( $value ) )
			return (int) $value;
		if( 1 !== preg_match( '/^(?:(\d+):)?(\d{1,2}):(\d{1,2})$/', $value, $matches ) )
			return NULL;
		return (int) $matches[1] * 3600 + (int) $matches[2] * 60 + (int) $matches[3];
	}

	/**
	 *	Parses explicit flag, accepting "true", "yes", "explicit" and "false", "no", "clean".
	 *	@param		string		$value		Value to parse
	 *	@return		?bool
	 */
	public static function parseExplicit( string $value ): ?bool
	{
		return match( strtolower( $value ) ){
			'true', 'yes', 'explicit'	=> TRUE,
			'false', 'no', 'clean'		=> FALSE,
			default						=> NULL,
		};
	}

	/**
	 *	Appends itunes:* elements of channel.
	 *	@param		Channel		$channel		Channel model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderChannel( Channel $channel, DOMElement $node ): void
	{
		$data	= $channel->getExtension( ChannelData::class );
		if( NULL === $data )
			return;
		$this->appendOptional( $node, 'title', $data->getTitle() );
		$this->appendOptional( $node, 'author', $data->getAuthor() );
		$owner	= $data->getOwner();
		if( NULL !== $owner ){
			$ownerNode	= $this->append( $node, 'owner' );
			$this->appendOptional( $ownerNode, 'name', $owner->name );
			$this->append( $ownerNode, 'email', $owner->email );
		}
		if( Dom::isFilled( $data->getImage() ) )
			$this->append( $node, 'image', NULL, ['href' => $data->getImage()] );
		foreach( $data->getCategories() as $category ){
			$categoryNode	= $this->append( $node, 'category', NULL, ['text' => $category->name] );
			if( Dom::isFilled( $category->subcategory ) )
				$this->append( $categoryNode, 'category', NULL, ['text' => $category->subcategory] );
		}
		if( NULL !== $data->getExplicit() )
			$this->append( $node, 'explicit', $data->getExplicit() ? 'true' : 'false' );
		$this->appendOptional( $node, 'type', $data->getType() );
		$this->appendOptional( $node, 'new-feed-url', $data->getNewFeedUrl() );
		if( $data->isBlocked() )
			$this->append( $node, 'block', 'Yes' );
		if( $data->isComplete() )
			$this->append( $node, 'complete', 'Yes' );
	}

	/**
	 *	Appends itunes:* elements of item.
	 *	@param		Item		$item		Item model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderItem( Item $item, DOMElement $node ): void
	{
		$data	= $item->getExtension( ItemData::class );
		if( NULL === $data )
			return;
		$this->appendOptional( $node, 'title', $data->getTitle() );
		if( NULL !== $data->getDuration() )
			$this->append( $node, 'duration', (string) $data->getDuration() );
		if( Dom::isFilled( $data->getImage() ) )
			$this->append( $node, 'image', NULL, ['href' => $data->getImage()] );
		if( NULL !== $data->getExplicit() )
			$this->append( $node, 'explicit', $data->getExplicit() ? 'true' : 'false' );
		if( NULL !== $data->getSeason() )
			$this->append( $node, 'season', (string) $data->getSeason() );
		if( NULL !== $data->getEpisode() )
			$this->append( $node, 'episode', (string) $data->getEpisode() );
		$this->appendOptional( $node, 'episodeType', $data->getEpisodeType() );
		if( $data->isBlocked() )
			$this->append( $node, 'block', 'Yes' );
	}

	/**
	 *	Returns classes of extension data stored in channel or items.
	 *	@return		list<class-string>
	 */
	protected function getDataClasses(): array
	{
		return [ChannelData::class, ItemData::class];
	}

	/**
	 *	Parses itunes:category with optional nested subcategory.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@return		?Category
	 */
	protected function parseCategory( DOMElement $element ): ?Category
	{
		$name	= trim( $element->getAttribute( 'text' ) );
		if( '' === $name )
			return NULL;
		$subcategory	= NULL;
		foreach( Dom::getChildren( $element, self::NAMESPACE_URI ) as $child )
			if( 'category' === $child->localName )
				$subcategory	= Dom::nullIfEmpty( $child->getAttribute( 'text' ) );
		return new Category( $name, $subcategory );
	}

	/**
	 *	Parses itunes:owner into person, NULL if no email is given.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@return		?Person
	 */
	protected function parseOwner( DOMElement $element ): ?Person
	{
		$name	= NULL;
		$email	= '';
		foreach( Dom::getChildren( $element, self::NAMESPACE_URI ) as $child ){
			if( 'name' === $child->localName )
				$name	= Dom::nullIfEmpty( Dom::getText( $child ) );
			if( 'email' === $child->localName )
				$email	= Dom::getText( $child );
		}
		return '' !== $email ? new Person( $email, $name ) : NULL;
	}
}
