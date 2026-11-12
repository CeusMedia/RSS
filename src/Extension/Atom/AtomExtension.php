<?php
declare( strict_types=1 );

/**
 *	Extension for Atom elements in RSS feeds (atom:link).
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
namespace CeusMedia\RSS\Extension\Atom;

use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Model\Channel;
use DOMElement;
use InvalidArgumentException;

/**
 *	Extension for Atom elements in RSS feeds (atom:link).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class AtomExtension extends AbstractExtension
{
	/**	Namespace URI of Atom elements */
	public const string NAMESPACE_URI	= 'http://www.w3.org/2005/Atom';

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
		return 'atom';
	}

	/**
	 *	Parses atom:link of channel into Atom extension data.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Channel		$channel		Channel model
	 */
	public function parseChannelElement( DOMElement $element, Channel $channel ): void
	{
		if( 'link' !== $element->localName )
			return;
		$link	= self::parseLink( $element );
		if( NULL === $link )
			return;
		$data	= $channel->getExtension( ChannelData::class ) ?? new ChannelData();
		$channel->setExtension( $data->addLink( $link ) );
	}

	/**
	 *	Parses atom:link element, also used by AtomParser.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@return		?Link
	 */
	public static function parseLink( DOMElement $element ): ?Link
	{
		$length	= trim( $element->getAttribute( 'length' ) );
		$rel	= trim( $element->getAttribute( 'rel' ) );
		try{
			return new Link(
				trim( $element->getAttribute( 'href' ) ),
				'' !== $rel ? $rel : 'alternate',
				'' !== trim( $element->getAttribute( 'type' ) ) ? trim( $element->getAttribute( 'type' ) ) : NULL,
				'' !== trim( $element->getAttribute( 'title' ) ) ? trim( $element->getAttribute( 'title' ) ) : NULL,
				ctype_digit( $length ) ? (int) $length : NULL,
			);
		}
		catch( InvalidArgumentException ){
			return NULL;
		}
	}

	/**
	 *	Appends atom:link elements of channel.
	 *	@param		Channel		$channel		Channel model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderChannel( Channel $channel, DOMElement $node ): void
	{
		foreach( $channel->getExtension( ChannelData::class )?->getLinks() ?? [] as $link )
			$this->append( $node, 'link', NULL, [
				'href'		=> $link->href,
				'rel'		=> $link->rel,
				'type'		=> $link->type,
				'title'		=> $link->title,
				'length'	=> NULL !== $link->length ? (string) $link->length : NULL,
			] );
	}

	/**
	 *	Returns classes of extension data stored in channel or items.
	 *	@return		list<class-string>
	 */
	protected function getDataClasses(): array
	{
		return [ChannelData::class];
	}
}
