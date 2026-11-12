<?php
declare( strict_types=1 );

/**
 *	Base class of RSS extensions.
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
namespace CeusMedia\RSS\Extension;

use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use DOMElement;
use DOMException;

/**
 *	Base class of RSS extensions.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
abstract class AbstractExtension implements ExtensionInterface
{
	/**
	 *	Indicates whether channel or any of its items has data of this extension.
	 *	@param		Channel		$channel		Channel model
	 *	@return		bool
	 */
	public function isUsedBy( Channel $channel ): bool
	{
		foreach( $this->getDataClasses() as $class ){
			if( $channel->hasExtension( $class ) )
				return TRUE;
			foreach( $channel->getItems() as $item )
				if( $item->hasExtension( $class ) )
					return TRUE;
		}
		return FALSE;
	}

	/**
	 *	Parses an element of this extension found as child of <channel>. Does nothing by default.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Channel		$channel		Channel model
	 */
	public function parseChannelElement( DOMElement $element, Channel $channel ): void
	{
	}

	/**
	 *	Parses an element of this extension found as child of <item>. Does nothing by default.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Item		$item		Item model
	 */
	public function parseItemElement( DOMElement $element, Item $item ): void
	{
	}

	/**
	 *	Appends extension elements of channel to <channel> node. Does nothing by default.
	 *	@param		Channel		$channel		Channel model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderChannel( Channel $channel, DOMElement $node ): void
	{
	}

	/**
	 *	Appends extension elements of item to <item> node. Does nothing by default.
	 *	@param		Item		$item		Item model
	 *	@param		DOMElement		$node		DOM element
	 */
	public function renderItem( Item $item, DOMElement $node ): void
	{
	}

	/**
	 *	Appends element of this extension's namespace.
	 *	@param		DOMElement					$parent		Parent DOM element
	 *	@param		string						$name			Local name, prefix is added
	 *	@param		?string						$value			Text content, NULL for none
	 *	@param		array<string, ?string>		$attributes		Attributes, NULL values are skipped
	 *	@return		DOMElement
	 *	@throws		DOMException
	 */
	protected function append( DOMElement $parent, string $name, ?string $value = NULL, array $attributes = [] ): DOMElement
	{
		return Dom::append( $parent, $this->getPrefix().':'.$name, $value, $attributes, $this->getNamespace() );
	}

	/**
	 *	Appends element only if value is not empty.
	 *	@param		DOMElement		$parent		Parent DOM element
	 *	@param		string		$name		Name
	 *	@param		?string		$value		Value to parse
	 *	@throws		DOMException
	 */
	protected function appendOptional( DOMElement $parent, string $name, ?string $value ): void
	{
		if( Dom::isFilled( $value ) )
			$this->append( $parent, $name, $value );
	}

	/**
	 *	Returns classes of extension data stored in channel or items.
	 *	@return		list<class-string>
	 */
	abstract protected function getDataClasses(): array;
}
