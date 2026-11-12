<?php
declare( strict_types=1 );

/**
 *	Atom link (atom:link) as value object.
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

use InvalidArgumentException;

/**
 *	Atom link (atom:link) as value object.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class Link
{
	/**
	 *	Creates Atom link.
	 *	@param		string		$href		URL
	 *	@param		string		$rel		Relation, like "self", "alternate", "hub" or "enclosure"
	 *	@param		?string		$type		MIME type
	 *	@param		?string		$title		Title
	 *	@param		?int		$length		Length in bytes (for enclosures)
	 *	@throws		InvalidArgumentException	if href or rel is empty or length is negative
	 */
	public function __construct(
		public string $href,
		public string $rel = 'alternate',
		public ?string $type = NULL,
		public ?string $title = NULL,
		public ?int $length = NULL,
	)
	{
		if( '' === trim( $href ) )
			throw new InvalidArgumentException( 'Link href cannot be empty' );
		if( '' === trim( $rel ) )
			throw new InvalidArgumentException( 'Link rel cannot be empty' );
		if( NULL !== $length && $length < 0 )
			throw new InvalidArgumentException( 'Link length cannot be negative' );
	}
}
