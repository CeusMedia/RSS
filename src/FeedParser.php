<?php
declare( strict_types=1 );

/**
 *	Parser for RSS 2.0 and Atom 1.0 feeds with automatic format detection.
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
use CeusMedia\RSS\Model\Channel;
use DOMElement;

/**
 *	Parser for RSS 2.0 and Atom 1.0 feeds with automatic format detection.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class FeedParser
{
	/**	Format RSS 2.0 */
	public const string FORMAT_RSS		= 'rss';
	/**	Format Atom 1.0 */
	public const string FORMAT_ATOM		= 'atom';

	/**
	 *	Detects format of feed, without parsing its content.
	 *	@param		string		$xml		XML string
	 *	@return		string		Format, see constants FORMAT_*
	 *	@throws		ParseException			if XML is invalid or format is not supported
	 */
	public static function detect( string $xml ): string
	{
		return self::detectRoot( Dom::load( $xml ) );
	}

	/**
	 *	Parses RSS 2.0 or Atom 1.0 feed and returns channel model.
	 *	Atom feeds are mapped to the RSS models, see AtomParser.
	 *	@param		string			$xml			Feed XML
	 *	@param		?Extensions		$extensions		Extensions to parse namespaced elements, default: all built-in extensions
	 *	@return		Channel
	 *	@throws		ParseException					if XML is invalid or format is not supported
	 */
	public static function parse( string $xml, ?Extensions $extensions = NULL ): Channel
	{
		$root	= Dom::load( $xml );
		return match( self::detectRoot( $root ) ){
			self::FORMAT_ATOM	=> AtomParser::parseRoot( $root, $extensions ),
			default				=> Parser::parseRoot( $root, $extensions ),
		};
	}

	/**
	 *	Detects format of feed by its root element.
	 *	@param		DOMElement		$root		Root element of document
	 *	@return		string
	 *	@throws		ParseException			if format is not supported
	 */
	protected static function detectRoot( DOMElement $root ): string
	{
		if( 'rss' === $root->localName && NULL === $root->namespaceURI )
			return self::FORMAT_RSS;
		if( 'feed' === $root->localName && AtomParser::NAMESPACE_URI === $root->namespaceURI )
			return self::FORMAT_ATOM;
		throw new ParseException( 'Unsupported feed format: root element is <'.$root->nodeName.'>, expected <rss> (RSS 2.0) or <feed> (Atom 1.0)' );
	}
}
