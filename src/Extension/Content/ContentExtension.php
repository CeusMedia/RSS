<?php
declare( strict_types=1 );

/**
 *	Extension for full item content (content:encoded).
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
namespace CeusMedia\RSS\Extension\Content;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Model\Item;
use DOMElement;

/**
 *	Extension for full item content (content:encoded).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class ContentExtension extends AbstractExtension
{
	/**	Namespace URI of content module */
	public const string NAMESPACE_URI	= 'http://purl.org/rss/1.0/modules/content/';

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
		return 'content';
	}

	/**
	 *	Parses content:encoded of item into content extension data.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Item		$item		Item model
	 */
	public function parseItemElement( DOMElement $element, Item $item ): void
	{
		$value	= Dom::getText( $element );
		if( 'encoded' === $element->localName && '' !== $value )
			$item->setExtension( new ItemData( $value ) );
	}

	/**
	 *	Appends content:encoded of item.
	 *	@param		Item		$item		Item model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderItem( Item $item, DOMElement $node ): void
	{
		$this->appendOptional( $node, 'encoded', $item->getExtension( ItemData::class )?->encoded );
	}

	/**
	 *	Returns classes of extension data stored in channel or items.
	 *	@return		list<class-string>
	 */
	protected function getDataClasses(): array
	{
		return [ItemData::class];
	}
}
