<?php
declare( strict_types=1 );

/**
 *	DOM helpers shared by renderer, parsers and extensions.
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

use CeusMedia\Common\XML\DOM\SyntaxValidator;
use CeusMedia\RSS\Exception\ParseException;
use DOMDocument;
use DOMElement;
use DOMException;
use DOMNode;

/**
 *	DOM helpers shared by renderer, parsers and extensions.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final class Dom
{
	/**
	 *	Appends element with text content and attributes to parent node.
	 *	Values are sanitized and escaped by DOM.
	 *	@param		DOMDocument|DOMElement		$parent			Parent node
	 *	@param		string						$name			Element name, qualified (prefix:name) if namespace is given
	 *	@param		?string						$value			Text content, NULL for none
	 *	@param		array<string, ?string>		$attributes		Map of attributes, NULL values are skipped
	 *	@param		?string						$namespace		Namespace URI for extension elements
	 *	@return		DOMElement
	 *	@throws		DOMException
	 */
	public static function append( DOMDocument|DOMElement $parent, string $name, ?string $value = NULL, array $attributes = [], ?string $namespace = NULL ): DOMElement
	{
		$document	= $parent instanceof DOMDocument ? $parent : $parent->ownerDocument;
		if( NULL === $document )
			throw new DOMException( 'Parent node has no document' );
		$element	= NULL !== $namespace ? $document->createElementNS( $namespace, $name ) : $document->createElement( $name );
		if( NULL !== $value )
			$element->appendChild( $document->createTextNode( self::sanitize( $value ) ) );
		foreach( $attributes as $key => $attributeValue )
			if( NULL !== $attributeValue )
				$element->setAttribute( $key, self::sanitize( $attributeValue ) );
		$parent->appendChild( $element );
		return $element;
	}

	/**
	 *	Returns child elements of a parent element.
	 *	@param		DOMNode			$parent		Parent DOM element
	 *	@param		string|FALSE|NULL	$namespace	Namespace URI, NULL for elements without namespace, FALSE for all
	 *	@return		list<DOMElement>
	 */
	public static function getChildren( DOMNode $parent, string|FALSE|NULL $namespace = NULL ): array
	{
		$list	= [];
		foreach( $parent->childNodes as $node )
			if( $node instanceof DOMElement && ( FALSE === $namespace || $namespace === $node->namespaceURI ) )
				$list[]	= $node;
		return $list;
	}

	/**
	 *	Loads XML string into DOM using the syntax validator of CeusMedia/Common,
	 *	converting all libxml errors into a parse exception.
	 *	External entities and network access are disabled.
	 *	@param		string		$xml		XML string
	 *	@return		DOMElement	Root element
	 *	@throws		ParseException
	 */
	public static function load( string $xml ): DOMElement
	{
		if( '' === trim( $xml ) )
			throw new ParseException( 'Invalid XML: empty string given' );
		$validator	= new SyntaxValidator();
		if( !$validator->validate( $xml ) )
			throw new ParseException( 'Invalid XML: '.str_replace( PHP_EOL, '; ', (string) $validator->getErrors() ) );
		$root	= $validator->getDocument()->documentElement;
		if( NULL === $root )
			throw new ParseException( 'Invalid XML: missing root element' );
		return $root;
	}

	/**
	 *	Returns trimmed text content of element.
	 *	@param		DOMNode		$node		DOM element
	 *	@return		string
	 */
	public static function getText( DOMNode $node ): string
	{
		return trim( $node->textContent );
	}

	/**
	 *	Indicates whether value is not NULL and not empty or whitespace only.
	 *	@param		?string		$value		Value to parse
	 *	@return		bool
	 */
	public static function isFilled( ?string $value ): bool
	{
		return NULL !== $value && '' !== trim( $value );
	}

	/**
	 *	Returns trimmed value, or NULL if empty.
	 *	@param		?string		$value		Value to parse
	 *	@return		?string
	 */
	public static function nullIfEmpty( ?string $value ): ?string
	{
		$value	= trim( $value ?? '' );
		return '' !== $value ? $value : NULL;
	}

	/**
	 *	Replaces invalid UTF-8 sequences by U+FFFD and removes characters not allowed in XML 1.0.
	 *	@param		string		$value		Value to parse
	 *	@return		string
	 */
	public static function sanitize( string $value ): string
	{
		if( 1 !== preg_match( '//u', $value ) )
			$value	= htmlspecialchars_decode( htmlspecialchars( $value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ), ENT_NOQUOTES );
		$pattern	= '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';
		return preg_replace( $pattern, '', $value ) ?? '';
	}
}
