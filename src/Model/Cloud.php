<?php
declare( strict_types=1 );

/**
 *	Cloud (rssCloud) settings of a channel as value object.
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
 *	Cloud (rssCloud) settings of a channel as value object.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class Cloud
{
	/**
	 *	Creates cloud settings.
	 *	@param		string		$domain					Host name of cloud service
	 *	@param		int			$port					Port of cloud service (1-65535)
	 *	@param		string		$path					Path of cloud service
	 *	@param		string		$registerProcedure		Name of procedure to register for notifications
	 *	@param		string		$protocol				Protocol, like xml-rpc, soap or http-post
	 *	@throws		InvalidArgumentException			if domain or protocol is empty or port is out of range
	 */
	public function __construct(
		public string $domain,
		public int $port,
		public string $path,
		public string $registerProcedure,
		public string $protocol,
	)
	{
		if( '' === trim( $domain ) )
			throw new InvalidArgumentException( 'Cloud domain cannot be empty' );
		if( $port < 1 || $port > 65535 )
			throw new InvalidArgumentException( 'Cloud port must be between 1 and 65535' );
		if( '' === trim( $protocol ) )
			throw new InvalidArgumentException( 'Cloud protocol cannot be empty' );
	}
}
