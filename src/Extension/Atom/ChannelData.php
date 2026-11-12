<?php
declare( strict_types=1 );

/**
 *	Atom links of a channel, like rel="self" or WebSub hub.
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

/**
 *	Atom links of a channel, like rel="self" or WebSub hub.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class ChannelData
{
	/** @var list<Link> Atom links */
	protected array $links	= [];

	/**
	 *	Creates Atom data with links.
	 *	@param		list<Link>		$links		Atom links
	 */
	public function __construct( array $links = [] )
	{
		foreach( $links as $link )
			$this->addLink( $link );
	}

	/**
	 *	Creates data with link to the feed itself, as recommended by the W3C feed validator.
	 *	@param		string		$url		URL of the feed
	 *	@param		string		$type		MIME type of the feed
	 *	@return		self
	 */
	public static function self( string $url, string $type = 'application/rss+xml' ): self
	{
		return new self( [new Link( $url, 'self', $type )] );
	}

	/**
	 *	Adds an Atom link.
	 *	@param		Link		$link		Atom link
	 *	@return		static
	 */
	public function addLink( Link $link ): static
	{
		$this->links[]	= $link;
		return $this;
	}

	/**
	 *	Returns all Atom links.
	 *	@return		list<Link>
	 */
	public function getLinks(): array
	{
		return $this->links;
	}

	/**
	 *	Returns links having the given relation.
	 *	@param		string		$rel		Relation, like "self" or "hub"
	 *	@return		list<Link>
	 */
	public function getLinksByRel( string $rel ): array
	{
		return array_values( array_filter( $this->links, static fn( Link $link ): bool => $rel === $link->rel ) );
	}

	/**
	 *	Returns URL of the feed itself (link with rel="self").
	 *	@return		?string
	 */
	public function getSelfUrl(): ?string
	{
		return $this->getLinksByRel( 'self' )[0]->href ?? NULL;
	}
}
