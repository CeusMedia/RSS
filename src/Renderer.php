<?php
declare( strict_types=1 );

/**
 *	Renderer for RSS 2.0 feeds.
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

use CeusMedia\RSS\Exception\RenderException;
use CeusMedia\RSS\Extension\ExtensionInterface;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Model\Category;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;
use DateTimeImmutable;
use DateTimeInterface;
use DOMDocument;
use DOMElement;
use DOMException;

/**
 *	Renderer for RSS 2.0 feeds.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Renderer
{
	/**	Value of version attribute of root element */
	public const string VERSION		= '2.0';

	/**
	 *	Renders channel model to RSS 2.0 XML string.
	 *
	 *	All values are escaped. Characters not allowed in XML 1.0 are removed,
	 *	invalid UTF-8 sequences are replaced by U+FFFD.
	 *	Namespaces of extensions are declared on the root element, only if the channel uses them.
	 *	@param		Channel			$channel		Channel model to render
	 *	@param		bool			$useTabs		Flag: indent using tabs instead of 2 spaces
	 *	@param		?Extensions		$extensions		Extensions to render extension data, default: all built-in extensions
	 *	@return		string
	 *	@throws		RenderException					if required data is missing
	 */
	public static function render( Channel $channel, bool $useTabs = FALSE, ?Extensions $extensions = NULL ): string
	{
		$used		= ( $extensions ?? Extensions::default() )->getUsedBy( $channel );
		$document	= new DOMDocument( '1.0', 'UTF-8' );
		$document->formatOutput	= TRUE;
		try{
			$rss	= Dom::append( $document, 'rss', NULL, ['version' => self::VERSION] );
			foreach( $used as $extension )
				$rss->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:'.$extension->getPrefix(), $extension->getNamespace() );
			self::renderChannel( $rss, $channel, $used );
		}
		catch( DOMException $e ){
			throw new RenderException( 'Rendering XML failed: '.$e->getMessage(), 0, $e );
		}
		$xml	= $document->saveXML();
		if( FALSE === $xml )
			throw new RenderException( 'Rendering XML failed' );
		return $useTabs ? self::indentWithTabs( $xml ) : $xml;
	}

	/**
	 *	Appends category elements with optional domain.
	 *	@param		DOMElement			$parent		Parent DOM element
	 *	@param		list<Category>		$categories		Categories
	 *	@throws		DOMException
	 */
	protected static function appendCategories( DOMElement $parent, array $categories ): void
	{
		foreach( $categories as $category )
			Dom::append( $parent, 'category', $category->name, ['domain' => $category->domain] );
	}

	/**
	 *	Appends element only if value is not empty.
	 *	@param		DOMElement		$parent		Parent DOM element
	 *	@param		string		$name		Name
	 *	@param		?string		$value		Value to parse
	 *	@throws		DOMException
	 */
	protected static function appendOptional( DOMElement $parent, string $name, ?string $value ): void
	{
		if( Dom::isFilled( $value ) )
			Dom::append( $parent, $name, $value );
	}

	/**
	 *	Formats date in RFC 822 format, keeping its timezone.
	 *	@param		DateTimeInterface		$date		Date
	 *	@return		string
	 */
	protected static function formatDate( DateTimeInterface $date ): string
	{
		return $date->format( DATE_RSS );
	}

	/**
	 *	Replaces indentation by 2 spaces with tabs.
	 *	@param		string		$xml		XML string
	 *	@return		string
	 */
	protected static function indentWithTabs( string $xml ): string
	{
		$lines	= explode( "\n", $xml );
		foreach( $lines as $nr => $line ){
			$lines[$nr]	= preg_replace_callback(
				'/^(  )+/',
				static fn( array $matches ): string => str_repeat( "\t", intdiv( strlen( $matches[0] ), 2 ) ),
				$line
			) ?? $line;
		}
		return implode( "\n", $lines );
	}

	/**
	 *	Renders <channel> element with all channel elements, extension elements and items.
	 *	@param		DOMElement					$rss
	 *	@param		Channel						$channel		Channel model
	 *	@param		list<ExtensionInterface>	$extensions		Extensions used by channel
	 *	@throws		DOMException
	 *	@throws		RenderException
	 */
	protected static function renderChannel( DOMElement $rss, Channel $channel, array $extensions ): void
	{
		$node		= Dom::append( $rss, 'channel' );
		$required	= [
			'title'			=> $channel->getTitle(),
			'link'			=> $channel->getLink(),
			'description'	=> $channel->getDescription(),
		];
		foreach( $required as $name => $value ){
			if( !Dom::isFilled( $value ) )
				throw new RenderException( 'Channel '.$name.' cannot be empty' );
			Dom::append( $node, $name, $value );
		}
		self::appendOptional( $node, 'language', $channel->getLanguage() );
		self::appendOptional( $node, 'copyright', $channel->getCopyright() );
		self::appendOptional( $node, 'managingEditor', $channel->getManagingEditor()?->__toString() );
		self::appendOptional( $node, 'webMaster', $channel->getWebMaster()?->__toString() );
		if( NULL !== $channel->getPubDate() )
			Dom::append( $node, 'pubDate', self::formatDate( $channel->getPubDate() ) );
		$lastBuildDate	= $channel->getLastBuildDate() ?? new DateTimeImmutable();
		Dom::append( $node, 'lastBuildDate', self::formatDate( $lastBuildDate ) );
		self::appendCategories( $node, $channel->getCategories() );
		self::appendOptional( $node, 'generator', $channel->getGenerator() );
		self::appendOptional( $node, 'docs', $channel->getDocs() );

		$cloud	= $channel->getCloud();
		if( NULL !== $cloud )
			Dom::append( $node, 'cloud', NULL, [
				'domain'			=> $cloud->domain,
				'port'				=> (string) $cloud->port,
				'path'				=> $cloud->path,
				'registerProcedure'	=> $cloud->registerProcedure,
				'protocol'			=> $cloud->protocol,
			] );
		if( NULL !== $channel->getTtl() )
			Dom::append( $node, 'ttl', (string) $channel->getTtl() );
		$image	= $channel->getImage();
		if( NULL !== $image )
			self::renderImage( $node, $image, $channel );
		self::appendOptional( $node, 'rating', $channel->getRating() );

		$textInput	= $channel->getTextInput();
		if( NULL !== $textInput ){
			$textInputNode	= Dom::append( $node, 'textInput' );
			Dom::append( $textInputNode, 'title', $textInput->title );
			Dom::append( $textInputNode, 'description', $textInput->description );
			Dom::append( $textInputNode, 'name', $textInput->name );
			Dom::append( $textInputNode, 'link', $textInput->link );
		}
		if( [] !== $channel->getSkipHours() ){
			$skipHoursNode	= Dom::append( $node, 'skipHours' );
			foreach( $channel->getSkipHours() as $hour )
				Dom::append( $skipHoursNode, 'hour', (string) $hour );
		}
		if( [] !== $channel->getSkipDays() ){
			$skipDaysNode	= Dom::append( $node, 'skipDays' );
			foreach( $channel->getSkipDays() as $day )
				Dom::append( $skipDaysNode, 'day', $day );
		}
		foreach( $extensions as $extension )
			$extension->renderChannel( $channel, $node );
		foreach( $channel->getItems() as $nr => $item )
			self::renderItem( $node, $item, $nr, $extensions );
	}

	/**
	 *	Renders channel image.
	 *	Title and link are required by specification and fall back to channel title and link.
	 *	@param		DOMElement		$parent		Parent DOM element
	 *	@param		Image		$image		Image
	 *	@param		Channel		$channel		Channel model
	 *	@throws		DOMException
	 */
	protected static function renderImage( DOMElement $parent, Image $image, Channel $channel ): void
	{
		$title	= Dom::isFilled( $image->getTitle() ) ? $image->getTitle() : $channel->getTitle();
		$link	= Dom::isFilled( $image->getLink() ) ? $image->getLink() : $channel->getLink();
		$node	= Dom::append( $parent, 'image' );
		Dom::append( $node, 'url', $image->getUrl() );
		Dom::append( $node, 'title', $title );
		Dom::append( $node, 'link', $link );
		if( NULL !== $image->getWidth() )
			Dom::append( $node, 'width', (string) $image->getWidth() );
		if( NULL !== $image->getHeight() )
			Dom::append( $node, 'height', (string) $image->getHeight() );
		self::appendOptional( $node, 'description', $image->getDescription() );
	}

	/**
	 *	Renders <item> element with all item elements and extension elements.
	 *	@param		DOMElement					$parent		Parent DOM element
	 *	@param		Item						$item		Item model
	 *	@param		int							$nr				Index of item, for error messages
	 *	@param		list<ExtensionInterface>	$extensions		Extensions used by channel
	 *	@throws		DOMException
	 *	@throws		RenderException			if item has neither title nor description
	 */
	protected static function renderItem( DOMElement $parent, Item $item, int $nr, array $extensions ): void
	{
		if( !Dom::isFilled( $item->getTitle() ) && !Dom::isFilled( $item->getDescription() ) )
			throw new RenderException( 'Item #'.( $nr + 1 ).' must have a title or a description' );
		$node	= Dom::append( $parent, 'item' );
		self::appendOptional( $node, 'title', $item->getTitle() );
		self::appendOptional( $node, 'link', $item->getLink() );
		self::appendOptional( $node, 'description', $item->getDescription() );
		self::appendOptional( $node, 'author', $item->getAuthor()?->__toString() );
		self::appendCategories( $node, $item->getCategories() );
		self::appendOptional( $node, 'comments', $item->getComments() );
		$enclosure	= $item->getEnclosure();
		if( NULL !== $enclosure )
			Dom::append( $node, 'enclosure', NULL, [
				'url'		=> $enclosure->url,
				'length'	=> (string) $enclosure->length,
				'type'		=> $enclosure->type,
			] );
		$guid	= $item->getGuid();
		if( NULL !== $guid )
			Dom::append( $node, 'guid', $guid->value, [
				'isPermaLink'	=> $guid->isPermaLink ? 'true' : 'false',
			] );
		if( NULL !== $item->getPubDate() )
			Dom::append( $node, 'pubDate', self::formatDate( $item->getPubDate() ) );
		$source	= $item->getSource();
		if( NULL !== $source )
			Dom::append( $node, 'source', $source->title, ['url' => $source->url] );
		foreach( $extensions as $extension )
			$extension->renderItem( $item, $node );
	}
}
