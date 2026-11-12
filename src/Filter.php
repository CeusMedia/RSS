<?php
declare( strict_types=1 );

/**
 *	Factory of item filters for the combiner.
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
namespace CeusMedia\RSS;

use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use Closure;
use DateTimeInterface;

/**
 *	Factory of item filters for Combiner::addFilter().
 *	A filter is a closure receiving item and its channel, returning TRUE to keep the item.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Filter
{
	/**
	 *	Keeps items having at least one of the given categories (case-insensitive).
	 *	@param		string		...$names		Names
	 *	@return		Closure(Item, Channel): bool
	 */
	public static function category( string ...$names ): Closure
	{
		$names	= array_map( static fn( string $name ): string => mb_strtolower( trim( $name ) ), $names );
		return static function( Item $item ) use ( $names ): bool {
			foreach( $item->getCategories() as $category )
				if( in_array( mb_strtolower( $category->name ), $names, TRUE ) )
					return TRUE;
			return FALSE;
		};
	}

	/**
	 *	Inverts a filter.
	 *	@param		Closure(Item, Channel): bool	$filter
	 *	@return		Closure(Item, Channel): bool
	 */
	public static function not( Closure $filter ): Closure
	{
		return static fn( Item $item, Channel $channel ): bool => !$filter( $item, $channel );
	}

	/**
	 *	Keeps items whose title or description contains the given text (case-insensitive).
	 *	@param		string		$text		Text
	 *	@return		Closure(Item, Channel): bool
	 */
	public static function search( string $text ): Closure
	{
		$text	= mb_strtolower( trim( $text ) );
		return static function( Item $item ) use ( $text ): bool {
			$haystack	= mb_strtolower( ( $item->getTitle() ?? '' ).' '.strip_tags( $item->getDescription() ?? '' ) );
			return str_contains( $haystack, $text );
		};
	}

	/**
	 *	Keeps items published at or after the given date. Items without date are removed.
	 *	@param		DateTimeInterface		$date		Date
	 *	@return		Closure(Item, Channel): bool
	 */
	public static function since( DateTimeInterface $date ): Closure
	{
		$timestamp	= $date->getTimestamp();
		return static fn( Item $item ): bool => NULL !== $item->getPubDate() && $item->getPubDate()->getTimestamp() >= $timestamp;
	}
}
