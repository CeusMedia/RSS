<?php
declare( strict_types=1 );

/**
 *	Parser for RSS 2.0 feeds.
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
namespace CeusMedia\RSS;

use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Cloud;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use CeusMedia\RSS\Model\Source;
use CeusMedia\RSS\Model\TextInput;
use DateTimeImmutable;
use DOMElement;
use Exception;
use InvalidArgumentException;

/**
 *	Parser for RSS 2.0 feeds.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Parser
{
	/**
	 *	Parses RSS 2.0 XML string and returns channel model.
	 *
	 *	Parsing is lenient: unknown elements are ignored, invalid optional values
	 *	(like malformed dates or out-of-range image sizes) are skipped.
	 *	Elements of other namespaces are handled by registered extensions, others are ignored.
	 *	@param		string			$xml			RSS XML
	 *	@param		?Extensions		$extensions		Extensions to parse namespaced elements, default: all built-in extensions
	 *	@return		Channel
	 *	@throws		ParseException					if XML is invalid or not an RSS feed
	 */
	public static function parse( string $xml, ?Extensions $extensions = NULL ): Channel
	{
		return self::parseRoot( Dom::load( $xml ), $extensions );
	}

	/**
	 *	Parses already loaded RSS document, given by its root element.
	 *	@param		DOMElement		$root			Root element <rss>
	 *	@param		?Extensions		$extensions		Extensions to parse namespaced elements, default: all built-in extensions
	 *	@return		Channel
	 *	@throws		ParseException					if root element is not an RSS feed
	 */
	public static function parseRoot( DOMElement $root, ?Extensions $extensions = NULL ): Channel
	{
		$extensions	??= Extensions::default();
		if( 'rss' !== $root->localName || NULL !== $root->namespaceURI )
			throw new ParseException( 'Not an RSS feed: root element is <'.$root->nodeName.'>' );
		foreach( self::getChildren( $root ) as $node )
			if( 'channel' === $node->localName )
				return self::parseChannel( $node, $extensions );
		throw new ParseException( 'Invalid RSS feed: missing <channel> element' );
	}

	/**
	 *	Returns child elements without namespace, ignoring extension elements.
	 *	@param		DOMElement		$parent		Parent DOM element
	 *	@return		list<DOMElement>
	 */
	protected static function getChildren( DOMElement $parent ): array
	{
		return Dom::getChildren( $parent );
	}

	/**
	 *	Returns map of child element names and their text, for simple flat structures.
	 *	@param		DOMElement		$parent		Parent DOM element
	 *	@return		array<string, string>
	 */
	protected static function getChildrenText( DOMElement $parent ): array
	{
		$map	= [];
		foreach( self::getChildren( $parent ) as $node )
			$map[$node->localName ?? '']	= self::getText( $node );
		return $map;
	}

	/**
	 *	Returns trimmed text content of element.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		string
	 */
	protected static function getText( DOMElement $node ): string
	{
		return Dom::getText( $node );
	}

	/**
	 *	Returns trimmed value, or NULL if empty.
	 *	@param		string		$value		Value to parse
	 *	@return		?string
	 */
	protected static function nullIfEmpty( string $value ): ?string
	{
		return Dom::nullIfEmpty( $value );
	}

	/**
	 *	Parses category element, NULL if empty.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Category
	 */
	protected static function parseCategory( DOMElement $node ): ?Category
	{
		$name	= self::getText( $node );
		if( '' === $name )
			return NULL;
		return new Category( $name, self::nullIfEmpty( $node->getAttribute( 'domain' ) ) );
	}

	/**
	 *	Parses <channel> element with its items, passing namespaced elements to extensions.
	 *	@param		DOMElement		$channelNode		DOM element <channel>
	 *	@param		Extensions		$extensions		Extensions, default: all built-in extensions
	 *	@return		Channel
	 */
	protected static function parseChannel( DOMElement $channelNode, Extensions $extensions ): Channel
	{
		//  docs URL is only set if present in feed
		$channel	= ( new Channel() )->setDocs( NULL );
		foreach( Dom::getChildren( $channelNode, FALSE ) as $node ){
			if( NULL !== $node->namespaceURI ){
				$extensions->get( $node->namespaceURI )?->parseChannelElement( $node, $channel );
				continue;
			}
			$value	= self::getText( $node );
			switch( $node->localName ){
				case 'title':
					$channel->setTitle( $value );
					break;
				case 'link':
					$channel->setLink( $value );
					break;
				case 'description':
					$channel->setDescription( $value );
					break;
				case 'language':
					$channel->setLanguage( self::nullIfEmpty( $value ) );
					break;
				case 'copyright':
					$channel->setCopyright( self::nullIfEmpty( $value ) );
					break;
				case 'managingEditor':
					$channel->setManagingEditor( self::parsePerson( $value ) );
					break;
				case 'webMaster':
					$channel->setWebMaster( self::parsePerson( $value ) );
					break;
				case 'pubDate':
					$channel->setPubDate( self::parseDate( $value ) );
					break;
				case 'lastBuildDate':
					$channel->setLastBuildDate( self::parseDate( $value ) );
					break;
				case 'category':
					$category	= self::parseCategory( $node );
					if( NULL !== $category )
						$channel->addCategory( $category );
					break;
				case 'generator':
					$channel->setGenerator( self::nullIfEmpty( $value ) );
					break;
				case 'docs':
					$channel->setDocs( self::nullIfEmpty( $value ) );
					break;
				case 'cloud':
					$channel->setCloud( self::parseCloud( $node ) );
					break;
				case 'ttl':
					$channel->setTtl( ctype_digit( $value ) ? (int) $value : NULL );
					break;
				case 'image':
					$channel->setImage( self::parseImage( $node ) );
					break;
				case 'rating':
					$channel->setRating( self::nullIfEmpty( $value ) );
					break;
				case 'textInput':
				case 'textinput':
					$channel->setTextInput( self::parseTextInput( $node ) );
					break;
				case 'skipHours':
					$channel->setSkipHours( self::parseSkipHours( $node ) );
					break;
				case 'skipDays':
					$channel->setSkipDays( self::parseSkipDays( $node ) );
					break;
				case 'item':
					$channel->addItem( self::parseItem( $node, $extensions ) );
					break;
			}
		}
		return $channel;
	}

	/**
	 *	Parses cloud element, NULL if invalid.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Cloud
	 */
	protected static function parseCloud( DOMElement $node ): ?Cloud
	{
		try{
			return new Cloud(
				$node->getAttribute( 'domain' ),
				(int) $node->getAttribute( 'port' ),
				$node->getAttribute( 'path' ),
				$node->getAttribute( 'registerProcedure' ),
				$node->getAttribute( 'protocol' ),
			);
		}
		catch( InvalidArgumentException ){
			return NULL;
		}
	}

	/**
	 *	Parses date in RFC 822 format, also accepting common deviations like ISO 8601 (used by Atom).
	 *	@param		string		$value		Value to parse
	 *	@return		?DateTimeImmutable		NULL if empty or invalid
	 */
	public static function parseDate( string $value ): ?DateTimeImmutable
	{
		if( '' === $value )
			return NULL;
		$date	= DateTimeImmutable::createFromFormat( DATE_RSS, $value );
		if( FALSE !== $date )
			return $date;
		try{
			return new DateTimeImmutable( $value );
		}
		catch( Exception ){
			return NULL;
		}
	}

	/**
	 *	Parses enclosure element, NULL if invalid. Missing length becomes 0, missing type application/octet-stream.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Enclosure
	 */
	protected static function parseEnclosure( DOMElement $node ): ?Enclosure
	{
		$length	= trim( $node->getAttribute( 'length' ) );
		$type	= trim( $node->getAttribute( 'type' ) );
		try{
			return new Enclosure(
				trim( $node->getAttribute( 'url' ) ),
				ctype_digit( $length ) ? (int) $length : 0,
				'' !== $type ? $type : 'application/octet-stream',
			);
		}
		catch( InvalidArgumentException ){
			return NULL;
		}
	}

	/**
	 *	Parses image element, NULL if URL is missing. Invalid sizes are skipped.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Image
	 */
	protected static function parseImage( DOMElement $node ): ?Image
	{
		$data	= self::getChildrenText( $node );
		if( '' === ( $data['url'] ?? '' ) )
			return NULL;
		$image	= new Image(
			$data['url'],
			self::nullIfEmpty( $data['title'] ?? '' ),
			self::nullIfEmpty( $data['link'] ?? '' ),
		);
		$image->setDescription( self::nullIfEmpty( $data['description'] ?? '' ) );
		foreach( ['width' => $image->setWidth( ... ), 'height' => $image->setHeight( ... )] as $key => $setter ){
			try{
				$value	= $data[$key] ?? '';
				$setter( ctype_digit( $value ) ? (int) $value : NULL );
			}
			catch( InvalidArgumentException ){
			}
		}
		return $image;
	}

	/**
	 *	Parses <item> element, passing namespaced elements to extensions.
	 *	@param		DOMElement		$itemNode		DOM element <item>
	 *	@param		Extensions		$extensions		Extensions, default: all built-in extensions
	 *	@return		Item
	 */
	protected static function parseItem( DOMElement $itemNode, Extensions $extensions ): Item
	{
		$item	= new Item();
		foreach( Dom::getChildren( $itemNode, FALSE ) as $node ){
			if( NULL !== $node->namespaceURI ){
				$extensions->get( $node->namespaceURI )?->parseItemElement( $node, $item );
				continue;
			}
			$value	= self::getText( $node );
			switch( $node->localName ){
				case 'title':
					$item->setTitle( $value );
					break;
				case 'link':
					$item->setLink( self::nullIfEmpty( $value ) );
					break;
				case 'description':
					$item->setDescription( $value );
					break;
				case 'author':
					$item->setAuthor( self::parsePerson( $value ) );
					break;
				case 'category':
					$category	= self::parseCategory( $node );
					if( NULL !== $category )
						$item->addCategory( $category );
					break;
				case 'comments':
					$item->setComments( self::nullIfEmpty( $value ) );
					break;
				case 'enclosure':
					$item->setEnclosure( self::parseEnclosure( $node ) );
					break;
				case 'guid':
					//  according to RSS 2.0 specification, isPermaLink defaults to TRUE
					$isPermaLink	= 'false' !== strtolower( trim( $node->getAttribute( 'isPermaLink' ) ) );
					$item->setGuid( '' !== $value ? new Guid( $value, $isPermaLink ) : NULL );
					break;
				case 'pubDate':
					$item->setPubDate( self::parseDate( $value ) );
					break;
				case 'source':
					$url	= trim( $node->getAttribute( 'url' ) );
					$item->setSource( '' !== $url ? new Source( $value, $url ) : NULL );
					break;
			}
		}
		return $item;
	}

	/**
	 *	Parses person in RSS notation, NULL if empty.
	 *	@param		string		$value		Value to parse
	 *	@return		?Person
	 */
	protected static function parsePerson( string $value ): ?Person
	{
		return '' !== $value ? Person::fromString( $value ) : NULL;
	}

	/**
	 *	Parses skipDays element, skipping invalid day names.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		list<string>
	 */
	protected static function parseSkipDays( DOMElement $node ): array
	{
		$days	= [];
		foreach( self::getChildren( $node ) as $child ){
			$day	= ucfirst( strtolower( self::getText( $child ) ) );
			if( 'day' === $child->localName && in_array( $day, Channel::DAYS, TRUE ) )
				$days[]	= $day;
		}
		return $days;
	}

	/**
	 *	Parses skipHours element, skipping invalid hours.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		list<int>
	 */
	protected static function parseSkipHours( DOMElement $node ): array
	{
		$hours	= [];
		foreach( self::getChildren( $node ) as $child ){
			$hour	= self::getText( $child );
			if( 'hour' === $child->localName && ctype_digit( $hour ) && (int) $hour <= 23 )
				$hours[]	= (int) $hour;
		}
		return $hours;
	}

	/**
	 *	Parses textInput element, NULL if incomplete.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?TextInput
	 */
	protected static function parseTextInput( DOMElement $node ): ?TextInput
	{
		$data	= self::getChildrenText( $node );
		try{
			return new TextInput( $data['title'] ?? '', $data['description'] ?? '', $data['name'] ?? '', $data['link'] ?? '' );
		}
		catch( InvalidArgumentException ){
			return NULL;
		}
	}
}
