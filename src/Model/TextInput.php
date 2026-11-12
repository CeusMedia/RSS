<?php
declare( strict_types=1 );

/**
 *	Text input box of a channel as value object.
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

/**
 *	Text input box of a channel as value object.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class TextInput
{
	/**
	 *	Creates text input box.
	 *	@param		string		$title			Label of submit button
	 *	@param		string		$description	Explanation of text input
	 *	@param		string		$name			Name of text field
	 *	@param		string		$link			URL of script processing the request
	 *	@throws		InvalidArgumentException	if any value is empty
	 */
	public function __construct(
		public string $title,
		public string $description,
		public string $name,
		public string $link,
	)
	{
		foreach( ['title' => $title, 'description' => $description, 'name' => $name, 'link' => $link] as $key => $value )
			if( '' === trim( $value ) )
				throw new InvalidArgumentException( 'Text input '.$key.' cannot be empty' );
	}
}
