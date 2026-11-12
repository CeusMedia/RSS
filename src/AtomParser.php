<?php
declare( strict_types=1 );

/**
 *	Parser for Atom 1.0 feeds, mapping them to the RSS models.
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
use CeusMedia\RSS\Extension\Atom\AtomExtension;
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;
use CeusMedia\RSS\Extension\DublinCore\Data as DublinCoreData;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Enclosure;
use CeusMedia\RSS\Model\Guid;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Person;
use DOMElement;

/**
 *	Parser for Atom 1.0 feeds (RFC 4287), mapping them to the RSS models.
 *
 *	Mapping:
 *	- feed: title → title, subtitle → description, link (alternate) → link, updated → lastBuildDate,
 *	  rights → copyright, generator → generator, category → category, logo or icon → image,
 *	  author with email → managingEditor, author names → dc:creator, other links → atom:link
 *	- entry: title → title, link (alternate) → link, link (enclosure) → enclosure,
 *	  summary (or content) → description, content → content:encoded, id → guid,
 *	  published (or updated) → pubDate, author with email → author, author names → dc:creator,
 *	  category → category
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class AtomParser
{
	/**	Namespace URI of Atom 1.0 */
	public const string NAMESPACE_URI	= AtomExtension::NAMESPACE_URI;

	/**
	 *	Parses Atom XML string and returns channel model.
	 *	@param		string			$xml			Atom XML
	 *	@param		?Extensions		$extensions		Extensions to parse elements of other namespaces
	 *	@return		Channel
	 *	@throws		ParseException					if XML is invalid or not an Atom feed
	 */
	public static function parse( string $xml, ?Extensions $extensions = NULL ): Channel
	{
		return self::parseRoot( Dom::load( $xml ), $extensions );
	}

	/**
	 *	Parses already loaded Atom document, given by its root element.
	 *	@param		DOMElement		$root			Root element <feed>
	 *	@param		?Extensions		$extensions		Extensions to parse elements of other namespaces
	 *	@return		Channel
	 *	@throws		ParseException					if root element is not an Atom feed
	 */
	public static function parseRoot( DOMElement $root, ?Extensions $extensions = NULL ): Channel
	{
		if( 'feed' !== $root->localName || self::NAMESPACE_URI !== $root->namespaceURI )
			throw new ParseException( 'Not an Atom feed: root element is <'.$root->nodeName.'>' );
		$extensions	??= Extensions::default();
		$channel	= ( new Channel() )->setDocs( NULL );
		$atomLinks	= new AtomData();
		$creators	= new DublinCoreData();
		foreach( Dom::getChildren( $root, FALSE ) as $node ){
			if( self::NAMESPACE_URI !== $node->namespaceURI ){
				if( NULL !== $node->namespaceURI )
					$extensions->get( $node->namespaceURI )?->parseChannelElement( $node, $channel );
				continue;
			}
			switch( $node->localName ){
				case 'title':
					$channel->setTitle( self::parseText( $node ) );
					break;
				case 'subtitle':
					$channel->setDescription( self::parseText( $node ) );
					break;
				case 'link':
					self::parseFeedLink( $node, $channel, $atomLinks );
					break;
				case 'updated':
					$channel->setLastBuildDate( Parser::parseDate( Dom::getText( $node ) ) );
					break;
				case 'rights':
					$channel->setCopyright( Dom::nullIfEmpty( self::parseText( $node ) ) );
					break;
				case 'generator':
					$channel->setGenerator( Dom::nullIfEmpty( Dom::getText( $node ) ) );
					break;
				case 'category':
					$category	= self::parseCategory( $node );
					if( NULL !== $category )
						$channel->addCategory( $category );
					break;
				case 'logo':
					$channel->setImage( self::parseImage( $node ) ?? $channel->getImage() );
					break;
				case 'icon':
					if( NULL === $channel->getImage() )
						$channel->setImage( self::parseImage( $node ) );
					break;
				case 'author':
					[$person, $name]	= self::parsePerson( $node );
					if( NULL !== $person && NULL === $channel->getManagingEditor() )
						$channel->setManagingEditor( $person );
					if( NULL !== $name )
						$creators->addCreator( $name );
					break;
				case 'entry':
					$channel->addItem( self::parseEntry( $node, $extensions ) );
					break;
			}
		}
		if( [] !== $atomLinks->getLinks() )
			$channel->setExtension( $atomLinks );
		if( [] !== $creators->getCreators() )
			$channel->setExtension( $creators );
		return $channel;
	}

	/**
	 *	Parses Atom category, using term as name and scheme as domain.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Category
	 */
	protected static function parseCategory( DOMElement $node ): ?Category
	{
		$term	= trim( $node->getAttribute( 'term' ) );
		if( '' === $term )
			return NULL;
		return new Category( $term, Dom::nullIfEmpty( $node->getAttribute( 'scheme' ) ) );
	}

	/**
	 *	Parses Atom entry into item.
	 *	@param		DOMElement		$entry		Atom <entry> element
	 *	@param		Extensions		$extensions		Extensions, default: all built-in extensions
	 *	@return		Item
	 */
	protected static function parseEntry( DOMElement $entry, Extensions $extensions ): Item
	{
		$item		= new Item();
		$creators	= new DublinCoreData();
		$id			= NULL;
		$summary	= NULL;
		$content	= NULL;
		$published	= NULL;
		$updated	= NULL;
		foreach( Dom::getChildren( $entry, FALSE ) as $node ){
			if( self::NAMESPACE_URI !== $node->namespaceURI ){
				if( NULL !== $node->namespaceURI )
					$extensions->get( $node->namespaceURI )?->parseItemElement( $node, $item );
				continue;
			}
			switch( $node->localName ){
				case 'title':
					$item->setTitle( Dom::nullIfEmpty( self::parseText( $node ) ) );
					break;
				case 'link':
					self::parseEntryLink( $node, $item );
					break;
				case 'id':
					$id	= Dom::nullIfEmpty( Dom::getText( $node ) );
					break;
				case 'summary':
					$summary	= Dom::nullIfEmpty( self::parseText( $node ) );
					break;
				case 'content':
					$content	= Dom::nullIfEmpty( self::parseText( $node ) );
					break;
				case 'published':
					$published	= Parser::parseDate( Dom::getText( $node ) );
					break;
				case 'updated':
					$updated	= Parser::parseDate( Dom::getText( $node ) );
					break;
				case 'author':
					[$person, $name]	= self::parsePerson( $node );
					if( NULL !== $person && NULL === $item->getAuthor() )
						$item->setAuthor( $person );
					if( NULL !== $name )
						$creators->addCreator( $name );
					break;
				case 'category':
					$category	= self::parseCategory( $node );
					if( NULL !== $category )
						$item->addCategory( $category );
					break;
			}
		}
		$item->setDescription( $summary ?? $content );
		if( NULL !== $content )
			$item->setExtension( new ContentData( $content ) );
		$item->setPubDate( $published ?? $updated );
		if( NULL !== $id )
			$item->setGuid( new Guid( $id, $id === $item->getLink() ) );
		if( [] !== $creators->getCreators() )
			$item->setExtension( $creators );
		return $item;
	}

	/**
	 *	Applies link of entry: alternate link as item link, enclosure link as enclosure.
	 *	@param		DOMElement		$node		DOM element
	 *	@param		Item		$item		Item model
	 */
	protected static function parseEntryLink( DOMElement $node, Item $item ): void
	{
		$link	= AtomExtension::parseLink( $node );
		if( NULL === $link )
			return;
		if( 'alternate' === $link->rel && NULL === $item->getLink() )
			$item->setLink( $link->href );
		else if( 'enclosure' === $link->rel && NULL === $item->getEnclosure() )
			$item->setEnclosure( new Enclosure( $link->href, $link->length ?? 0, $link->type ?? 'application/octet-stream' ) );
	}

	/**
	 *	Applies link of feed: first alternate link as channel link, other links as Atom extension data.
	 *	@param		DOMElement		$node		DOM element
	 *	@param		Channel		$channel		Channel model
	 *	@param		AtomData		$atomLinks		Collector of Atom links of the feed
	 */
	protected static function parseFeedLink( DOMElement $node, Channel $channel, AtomData $atomLinks ): void
	{
		$link	= AtomExtension::parseLink( $node );
		if( NULL === $link )
			return;
		if( 'alternate' === $link->rel && '' === $channel->getLink() )
			$channel->setLink( $link->href );
		else
			$atomLinks->addLink( $link );
	}

	/**
	 *	Parses logo or icon of feed into image.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		?Image
	 */
	protected static function parseImage( DOMElement $node ): ?Image
	{
		$url	= Dom::getText( $node );
		return '' !== $url ? new Image( $url ) : NULL;
	}

	/**
	 *	Parses Atom person construct.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		array{0: ?Person, 1: ?string}		Person if email is given, and name
	 */
	protected static function parsePerson( DOMElement $node ): array
	{
		$name	= NULL;
		$email	= NULL;
		foreach( Dom::getChildren( $node, self::NAMESPACE_URI ) as $child ){
			if( 'name' === $child->localName )
				$name	= Dom::nullIfEmpty( Dom::getText( $child ) );
			if( 'email' === $child->localName )
				$email	= Dom::nullIfEmpty( Dom::getText( $child ) );
		}
		return [NULL !== $email ? new Person( $email, $name ) : NULL, $name];
	}

	/**
	 *	Returns content of Atom text construct. For type "xhtml", the inner markup of the wrapping div is returned.
	 *	@param		DOMElement		$node		DOM element
	 *	@return		string
	 */
	protected static function parseText( DOMElement $node ): string
	{
		if( 'xhtml' !== $node->getAttribute( 'type' ) )
			return Dom::getText( $node );
		$namespace	= 'http://www.w3.org/1999/xhtml';
		$children	= Dom::getChildren( $node, FALSE );
		//  content is wrapped by a div by specification, but some feeds omit it
		$container	= 1 === count( $children ) && 'div' === $children[0]->localName && $namespace === $children[0]->namespaceURI ? $children[0] : $node;
		$html		= '';
		foreach( $container->childNodes as $child )
			$html	.= $node->ownerDocument?->saveXML( $child );
		return trim( str_replace( ' xmlns="'.$namespace.'"', '', $html ) );
	}
}
