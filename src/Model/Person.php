<?php
declare( strict_types=1 );

/**
 *	Person (author, managing editor, web master) as value object.
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
 *	Person (author, managing editor, web master) as value object.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class Person implements Stringable
{
	/**
	 *	Creates person.
	 *	@param		string		$email		Email address, required by RSS 2.0
	 *	@param		?string		$name		Name, optional
	 *	@throws		InvalidArgumentException	if email is empty
	 */
	public function __construct(
		public string $email,
		public ?string $name = NULL,
	)
	{
		if( '' === trim( $email ) )
			throw new InvalidArgumentException( 'Email of person cannot be empty' );
	}

	/**
	 *	Creates person from RSS notation like "email@example.org (Full Name)".
	 *	@param		string		$value		Value to parse
	 *	@return		self
	 *	@throws		InvalidArgumentException	if value is empty
	 */
	public static function fromString( string $value ): self
	{
		$value	= trim( $value );
		if( 1 === preg_match( '/^(\S.*?)\s*\((.*)\)$/s', $value, $matches ) ){
			$name	= trim( $matches[2] );
			return new self( $matches[1], '' !== $name ? $name : NULL );
		}
		return new self( $value );
	}

	/**
	 *	Returns RSS notation like "email@example.org (Full Name)".
	 *	@return		string
	 */
	public function __toString(): string
	{
		$name	= trim( $this->name ?? '' );
		return $this->email.( '' !== $name ? ' ('.$name.')' : '' );
	}
}
