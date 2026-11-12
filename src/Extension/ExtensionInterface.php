<?php
declare( strict_types=1 );

/**
 *	Interface of RSS extensions (namespaced elements like itunes:*, content:*, dc:*).
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

use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use DOMElement;
use DOMException;

/**
 *	Interface of RSS extensions (namespaced elements like itunes:*, content:*, dc:*).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
interface ExtensionInterface
{
	/**
	 *	Returns namespace URI of extension elements.
	 *	@return		string
	 */
	public function getNamespace(): string;

	/**
	 *	Returns preferred namespace prefix used on rendering.
	 *	@return		string
	 */
	public function getPrefix(): string;

	/**
	 *	Indicates whether channel or any of its items has data of this extension.
	 *	Used by renderer to declare only needed namespaces.
	 *	@param		Channel		$channel		Channel model
	 *	@return		bool
	 */
	public function isUsedBy( Channel $channel ): bool;

	/**
	 *	Parses an element of this extension found as child of <channel>.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Channel		$channel		Channel model
	 */
	public function parseChannelElement( DOMElement $element, Channel $channel ): void;

	/**
	 *	Parses an element of this extension found as child of <item>.
	 *	@param		DOMElement		$element		DOM element of this extension
	 *	@param		Item		$item		Item model
	 */
	public function parseItemElement( DOMElement $element, Item $item ): void;

	/**
	 *	Appends extension elements of channel to <channel> node.
	 *	@param		Channel		$channel		Channel model
	 *	@param		DOMElement		$node		DOM element
	 *	@throws		DOMException
	 */
	public function renderChannel( Channel $channel, DOMElement $node ): void;

	/**
	 *	Appends extension elements of item to <item> node.
	 *	@param		Item		$item		Item model
	 *	@param		DOMElement		$node		DOM element
	 *	@throws		DOMException
	 */
	public function renderItem( Item $item, DOMElement $node ): void;
}
