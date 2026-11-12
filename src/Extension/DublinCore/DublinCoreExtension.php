<?php
declare( strict_types=1 );

/**
 *	Extension for Dublin Core elements (dc:creator).
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
namespace CeusMedia\RSS\Extension\DublinCore;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use DOMElement;

/**
 *	Extension for Dublin Core elements (dc:creator).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class DublinCoreExtension extends AbstractExtension
{
	/**	Namespace URI of Dublin Core elements */
	public const string NAMESPACE_URI	= 'http://purl.org/dc/elements/1.1/';

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
		return 'dc';
	}

	/**
	 *	Parses dc:creator of channel into Dublin Core data.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Channel		$channel		Channel model
	 */
	public function parseChannelElement( DOMElement $element, Channel $channel ): void
	{
		if( 'creator' === $element->localName )
			$channel->setExtension( ( $channel->getExtension( Data::class ) ?? new Data() )->addCreator( Dom::getText( $element ) ) );
	}

	/**
	 *	Parses dc:creator of item into Dublin Core data.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Item		$item		Item model
	 */
	public function parseItemElement( DOMElement $element, Item $item ): void
	{
		if( 'creator' === $element->localName )
			$item->setExtension( ( $item->getExtension( Data::class ) ?? new Data() )->addCreator( Dom::getText( $element ) ) );
	}

	/**
	 *	Appends dc:creator elements of channel.
	 *	@param		Channel		$channel		Channel model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderChannel( Channel $channel, DOMElement $node ): void
	{
		foreach( $channel->getExtension( Data::class )?->getCreators() ?? [] as $creator )
			$this->append( $node, 'creator', $creator );
	}

	/**
	 *	Appends dc:creator elements of item.
	 *	@param		Item		$item		Item model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderItem( Item $item, DOMElement $node ): void
	{
		foreach( $item->getExtension( Data::class )?->getCreators() ?? [] as $creator )
			$this->append( $node, 'creator', $creator );
	}

	/**
	 *	Returns classes of extension data stored in channel or items.
	 *	@return		list<class-string>
	 */
	protected function getDataClasses(): array
	{
		return [Data::class];
	}
}
