<?php
declare( strict_types=1 );

/**
 *	Category of a channel or item as value object.
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
namespace CeusMedia\RSS\Model;

use InvalidArgumentException;
use Stringable;

/**
 *	Category of a channel or item as value object.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class Category implements Stringable
{
	/**
	 *	Creates category.
	 *	@param		string		$name		Category name, may be a hierarchical path like "Sports/Football"
	 *	@param		?string		$domain		Taxonomy the category belongs to, optional
	 *	@throws		InvalidArgumentException	if name is empty
	 */
	public function __construct(
		public string $name,
		public ?string $domain = NULL,
	)
	{
		if( '' === trim( $name ) )
			throw new InvalidArgumentException( 'Category name cannot be empty' );
	}

	/**
	 *	Returns name of category.
	 *	@return		string
	 */
	public function __toString(): string
	{
		return $this->name;
	}
}
