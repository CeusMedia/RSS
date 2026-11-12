<?php
declare( strict_types=1 );

/**
 *	Storage of extension data for channels and items.
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

/**
 *	Storage of extension data for channels and items.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
trait ExtensionDataTrait
{
	/** @var array<class-string, object> Extension data, by class */
	protected array $extensionData	= [];

	/**
	 *	Returns data of an extension, like iTunes or Dublin Core.
	 *	@template	T of object
	 *	@param		class-string<T>		$class		Class of extension data, e.g. ITunes\ChannelData::class
	 *	@return		T|NULL
	 */
	public function getExtension( string $class ): ?object
	{
		$data	= $this->extensionData[$class] ?? NULL;
		return $data instanceof $class ? $data : NULL;
	}

	/**
	 *	Returns all extension data, keyed by class.
	 *	@return		array<class-string, object>
	 */
	public function getExtensions(): array
	{
		return $this->extensionData;
	}

	/**
	 *	Indicates whether data of an extension is set.
	 *	@param		class-string	$class		Class of extension data, e.g. ITunes\ChannelData::class
	 *	@return		bool
	 */
	public function hasExtension( string $class ): bool
	{
		return isset( $this->extensionData[$class] );
	}

	/**
	 *	Removes data of an extension.
	 *	@param		class-string	$class		Class of extension data, e.g. ITunes\ChannelData::class
	 *	@return		static
	 */
	public function removeExtension( string $class ): static
	{
		unset( $this->extensionData[$class] );
		return $this;
	}

	/**
	 *	Sets data of an extension. Existing data of the same class is replaced.
	 *	@param		object		$data		Extension data, e.g. new ITunes\ChannelData()
	 *	@return		static
	 */
	public function setExtension( object $data ): static
	{
		$this->extensionData[$data::class]	= $data;
		return $this;
	}
}
