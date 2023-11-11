<?php
/**
 *	...
 *
 *	Copyright (c) 2012-2023 Christian Würker / {@link https://ceusmedia.de/ Ceus Media}
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
 *	@copyright		2012-2023 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
namespace CeusMedia\RSS;

use CeusMedia\Common\Exception\IO as IoException;
use CeusMedia\Common\Net\Reader as NetReader;
use CeusMedia\RSS\Model\Channel;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2023 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Combiner
{
	protected array $channels	= [];

	public function add( string $rssXml ): self
	{
		$this->channels[]	= Parser::parse( $rssXml );
		return $this;
	}

	public function addChannel( Model\Channel $channel ): self
	{
		$this->channels[]	= $channel;
		return $this;
	}

	/**
	 *	@param		string		$url
	 *	@return		self
	 *	@throws		IoException
	 */
	public function addUrl( string $url ): self
	{
		$this->add( NetReader::readUrl( $url ) );
		return $this;
	}

	public function combine( int $limit = 0 ): array
	{
		$list	= array();
		foreach( $this->channels as $channel ){
			foreach( $channel->getItems() as $item ){
				$timestamp	= 0;
				if( $item->getDate() )
					$timestamp	= $item->getDate();
				if( !isset( $list[$timestamp] ) )
					$list[$timestamp]	= array();
				$list[$timestamp][]	= $item;
			}
		}
		krsort( $list );
		$items	= array();
		foreach( $list as $timestamp => $entries ){
			foreach( $entries as $entry )
				$items[]	= $entry;
		}
		if( $limit > 0 )
			$items	= array_slice( $items, 0, $limit );
		return $items;
	}

	public function render( Channel $channel, int $limit = 0 ): string
	{
		foreach( $this->combine( $limit ) as $item )
			$channel->addItem( $item );
		return Renderer::render( $channel );
	}
}
