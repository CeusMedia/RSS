<?php
/**
 *	...
 *
 *	Copyright (c) 2012-2015 Christian Würker / {@link http://ceusmedia.de/ Ceus Media}
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
 *	@package		CeusMedia_Rss
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Rss
 */
namespace CeusMedia\Rss;
/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia_Rss
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/Rss
 */
class Combiner{

	protected $channels	= array();

	public function add( $rss ){
		$this->channels[]	= \CeusMedia\Rss\Parser::parse( $rss );
	}

	public function addChannel( \CeusMedia\Rss\Model\Channel $channel ){
		$this->channels[]	= $channel;
	}

	public function addUrl( $url ){
		$this->add( \Net_Reader::readUrl( $url ) );
	}

	public function combine( $limit = 0 ){
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

	public function render( $channel, $limit = 0 ){
		foreach( $this->combine( $limit ) as $item )
			$channel->addItem( $item );
		return \CeusMedia\Rss\Renderer::render( $channel );
	}
}
?>
