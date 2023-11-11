<?php
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

use CeusMedia\RSS\Model\Channel;
use DateTime;
use DOMDocument;
use RuntimeException;

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
class Renderer
{
	public static string $version		= "2.0.11";

	public static function render( Channel $channel, bool $useTabs = FALSE ): string
	{
		$lines	= array();
		if( NULL !== $channel->getTitle() && !strlen( trim( $channel->getTitle() ) ) )
			throw new RuntimeException( 'Channel title cannot be empty' );
		if( NULL !== $channel->getLink() && !strlen( trim( $channel->getLink() ) ) )
			throw new RuntimeException( 'Channel link cannot be empty' );
		if( NULL !== $channel->getDescription() && !strlen( trim( $channel->getDescription() ) ) )
			throw new RuntimeException( 'Channel description cannot be empty' );
		$lines[]	= self::renderNode( 'title', $channel->getTitle() );
		$lines[]	= self::renderNode( 'link', $channel->getLink() );
		$lines[]	= self::renderNode( 'description', $channel->getDescription() );

		if( NULL !== $channel->getLanguage() && strlen( $channel->getLanguage() ) )
			$lines[]	= self::renderNode( 'language', $channel->getLanguage() );
		if( NULL !== $channel->getCopyright() && strlen( $channel->getCopyright() ) )
			$lines[]	= self::renderNode( 'copyright', $channel->getCopyright() );

		if( ( $manager = $channel->getManager() ) ){
			$manager		= $manager[0].( !empty( $manager[1] ) ? ' ('.$manager[1].')' : '' );
			$lines[]	= self::renderNode( 'managingEditor', $manager );
		}
		if( ( $admin = $channel->getAdmin() ) ){
			$admin		= $admin[0].( !empty( $admin[1] ) ? ' ('.$admin[1].')' : '' );
			$lines[]	= self::renderNode( 'webMaster', $admin );
		}
		if( NULL !== ( $date = $channel->getDate() ) ){
			if( is_int( $date ) )
				$lines[]	= self::renderNode( 'pubDate', date( 'r', $date ) );
//			else if( $date instanceof DateTime )
//				$lines[]	= self::renderNode( 'pubDate', $date->format( 'r' ) );
			else
				$lines[]	= self::renderNode( 'pubDate', $date );
		}
		$lines[]	= self::renderNode( 'lastBuildDate', date( 'r', time() ) );

		if( NULL !== $channel->getCategory() && strlen( $channel->getCategory() ) )
			$lines[]	= self::renderNode( 'category', $channel->getCategory() );
		if( NULL !== $channel->getGenerator() && strlen( $channel->getGenerator() ) )
			$lines[]	= self::renderNode( 'generator', $channel->getGenerator() );
		$lines[]	= self::renderNode( 'docs', "https://www.rssboard.org/rss-specification" );

		if( $channel->getCloud() )
			$lines[]	= self::renderNode( 'cloud', NULL, $channel->getCloud() );

		if( ( $image = $channel->getImage() ) ){
			$url		= self::renderNode( 'url', $image->getUrl() );
			$title		= self::renderNode( 'title', $image->getTitle() );
			$link		= self::renderNode( 'link', $image->getLink() );
			$desc		= self::renderNode( 'description', $image->getDescription() ?: '' );
			$width		= self::renderNode( 'width', $image->getWidth() ?: '' );
			$height		= self::renderNode( 'height', $image->getHeight() ?: '' );
			$content	= $url.$title.$link.$desc.$width.$height;
			$lines[]	= self::renderNode( 'image', $content, array(), FALSE );
		}
		foreach( $channel->getItems() as $item )
			$lines[]	= self::renderItem( $item );
		$xml	= '<rss version="'.self::$version.'"><channel>'.join( $lines ).'</channel></rss>';
		return self::formatXml( $xml, $useTabs );
	}

	protected static function formatXml( string $xml, bool $useTabs	= FALSE ): string
	{
		$document	= new DOMDocument();
		$document->preserveWhiteSpace	= FALSE;
		$document->loadXml( $xml );
		$document->formatOutput = TRUE;
		$xml	= $document->saveXml();
		if( !$useTabs )
			return $xml;
		$lines	= explode( "\n", $xml );
		foreach( $lines as $nr => $line )
			while( preg_match( "/^\t*  /", $lines[$nr] ) )
				$lines[$nr]	= preg_replace( "/^(\t*)  /", "\\1\t", $lines[$nr] );
		return implode( "\n", $lines );
	}

	protected static function renderNode( string $name, $value, array $attributes = [], bool $encode = TRUE ): string
	{
		$list	= [];
		foreach( $attributes as $key => $attrValue )
			if( $attrValue !== NULL )
				$list[]	= ' '.$key.'="'.$attrValue.'"';
		if( $encode )
			$value	= htmlspecialchars( (string) $value );
		return '<'.$name.join( $list ).'>'.$value.'</'.$name.'>';
	}

	protected static function renderItem( Model\Item $item ): string
	{
		$lines	= array();
		if( NULL === $item->getTitle() || !strlen( $item->getTitle() ) )
			throw new RuntimeException( 'Item title cannot be empty' );
		$lines[]	= self::renderNode( 'title', $item->getTitle() );
		if( NULL !== $item->getLink() && strlen( $item->getLink() ) )
			$lines[]	= self::renderNode( 'link', $item->getLink() );
		if( NULL !== $item->getContent() && strlen( $item->getContent() ) )
			$lines[]	= self::renderNode( 'description', $item->getContent() );
		if( ( $author = $item->getAuthor() ) ){
			$author		= $author[0].( !empty( $author[1] ) ? ' ('.$author[1].')' : '' );
			$lines[]	= self::renderNode( 'author', $author );
		}
		if( NULL !== $item->getCategory() && strlen( $item->getCategory() ) )
			$lines[]	= self::renderNode( 'category', $item->getCategory() );
		if( NULL !== ( $date = $item->getDate() ) ){
			if( is_int( $date ) )
				$lines[]	= self::renderNode( 'pubDate', date( 'r', $date ) );
//			else if( $date instanceof DateTime )
//				$lines[]	= self::renderNode( 'pubDate', $date->format( 'r' ) );
			else
				$lines[]	= self::renderNode( 'pubDate', $date );
		}
		if( ( $guid = $item->getId() ) ){
			$attributes	= array( 'isPermaLink' => $guid[1] ? 'true' : NULL );
			$lines[]	= self::renderNode( 'guid', $guid[0], $attributes );
		}
		if( ( $source = $item->getSource() ) )
			$lines[]	= self::renderNode( 'source', $source[0], ['url' => $source[1]] );
		return self::renderNode( 'item', join( $lines ), [], FALSE );
	}
}
