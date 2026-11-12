<?php
declare( strict_types=1 );

/**
 *	Registry of RSS extensions used by parser and renderer.
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

use CeusMedia\RSS\Extension\Atom\AtomExtension;
use CeusMedia\RSS\Extension\Content\ContentExtension;
use CeusMedia\RSS\Extension\DublinCore\DublinCoreExtension;
use CeusMedia\RSS\Extension\ITunes\ITunesExtension;
use CeusMedia\RSS\Model\Channel;

/**
 *	Registry of RSS extensions used by parser and renderer.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Extensions
{
	/** @var array<string, ExtensionInterface> Registered extensions, by namespace URI */
	protected array $extensions	= [];

	/**
	 *	Creates registry with given extensions.
	 *	@param		list<ExtensionInterface>	$extensions		Extensions, default: all built-in extensions
	 */
	public function __construct( array $extensions = [] )
	{
		foreach( $extensions as $extension )
			$this->add( $extension );
	}

	/**
	 *	Returns registry with all built-in extensions: Atom, Content, Dublin Core and iTunes.
	 *	@return		self
	 */
	public static function default(): self
	{
		return new self( [
			new AtomExtension(),
			new ContentExtension(),
			new DublinCoreExtension(),
			new ITunesExtension(),
		] );
	}

	/**
	 *	Returns empty registry: all extension elements are ignored.
	 *	@return		self
	 */
	public static function none(): self
	{
		return new self();
	}

	/**
	 *	Registers extension. An extension with the same namespace is replaced.
	 *	@param		ExtensionInterface		$extension		Extension
	 *	@return		static
	 */
	public function add( ExtensionInterface $extension ): static
	{
		$this->extensions[$extension->getNamespace()]	= $extension;
		return $this;
	}

	/**
	 *	Returns extension registered for namespace URI, NULL if none.
	 *	@param		string		$namespace		Namespace URI
	 *	@return		?ExtensionInterface
	 */
	public function get( string $namespace ): ?ExtensionInterface
	{
		return $this->extensions[$namespace] ?? NULL;
	}

	/**
	 *	Returns all registered extensions.
	 *	@return		list<ExtensionInterface>
	 */
	public function getAll(): array
	{
		return array_values( $this->extensions );
	}

	/**
	 *	Returns extensions having data in channel or its items.
	 *	@param		Channel		$channel		Channel model
	 *	@return		list<ExtensionInterface>
	 */
	public function getUsedBy( Channel $channel ): array
	{
		return array_values( array_filter( $this->extensions, static fn( ExtensionInterface $extension ): bool => $extension->isUsedBy( $channel ) ) );
	}
}
