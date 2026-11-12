<?php
declare( strict_types=1 );

/**
 *	Combines items of several feeds into one feed, sorted by date.
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

use CeusMedia\RSS\Exception\ParseException;
use CeusMedia\RSS\Exception\ReadException;
use CeusMedia\RSS\Exception\RenderException;
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Extension\Extensions;
use CeusMedia\RSS\Model\Channel;
use CeusMedia\RSS\Model\Item;
use CeusMedia\RSS\Model\Source;
use Closure;

/**
 *	Combines items of several feeds (RSS 2.0 or Atom 1.0) into one feed.
 *
 *	- Items are sorted by publication date, newest first. Items without date are placed last.
 *	- Duplicates (same GUID, or same link if no GUID) are removed, the newest is kept.
 *	- Items get a <source> referencing their original feed, if its URL is known.
 *	- Items can be filtered, see Filter.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Combiner
{
	/** @var list<array{channel: Channel, url: ?string}> Added channels with URL of their feed */
	protected array $channels	= [];

	/** @var array<string, ReadException|ParseException> Errors of reading feeds, by URL */
	protected array $errors		= [];

	/** @var list<Closure(Item, Channel): bool> Filters, all must accept an item */
	protected array $filters	= [];

	/** @var bool Flag: add <source> to items */
	protected bool $addSource	= TRUE;

	/** @var bool Flag: remove duplicates by GUID or link */
	protected bool $unique		= TRUE;

	/**
	 *	Creates combiner, optionally with own URL reader and extensions.
	 *	@param		?UrlReader		$reader			Reader for addUrl() and addUrls(), default: UrlReader with default settings
	 *	@param		?Extensions		$extensions		Extensions for parsing and rendering, default: all built-in extensions
	 */
	public function __construct(
		protected ?UrlReader $reader = NULL,
		protected ?Extensions $extensions = NULL,
	)
	{
	}

	/**
	 *	Adds feed from XML string (RSS 2.0 or Atom 1.0).
	 *	@param		string		$xml		Feed XML
	 *	@param		?string		$url		URL of feed, used for <source> of items
	 *	@return		static
	 *	@throws		ParseException
	 */
	public function add( string $xml, ?string $url = NULL ): static
	{
		return $this->addChannel( FeedParser::parse( $xml, $this->extensions ), $url );
	}

	/**
	 *	Adds channel model.
	 *	@param		Channel		$channel		Channel model
	 *	@param		?string		$url		URL of feed, used for <source> of items
	 *	@return		static
	 */
	public function addChannel( Channel $channel, ?string $url = NULL ): static
	{
		$this->channels[]	= ['channel' => $channel, 'url' => $url];
		return $this;
	}

	/**
	 *	Adds a filter. Only items accepted by all filters are combined.
	 *	@param		Closure(Item, Channel): bool	$filter		See Filter for predefined filters
	 *	@return		static
	 */
	public function addFilter( Closure $filter ): static
	{
		$this->filters[]	= $filter;
		return $this;
	}

	/**
	 *	Adds feed by reading it from URL. Fails on first error, see addUrls() for fault tolerant reading.
	 *	@param		string		$url		URL of feed
	 *	@param		?int		$timeout	Maximum duration of request in seconds, default: setting of reader
	 *	@return		static
	 *	@throws		ReadException			if URL is invalid, request failed or timed out
	 *	@throws		ParseException			if response is not a valid feed
	 */
	public function addUrl( string $url, ?int $timeout = NULL ): static
	{
		$reader	= NULL !== $timeout ? new UrlReader( $timeout ) : $this->getReader();
		return $this->add( $reader->fetch( $url )->content, $url );
	}

	/**
	 *	Adds feeds by reading them from URLs in parallel.
	 *	Feeds which cannot be read or parsed are skipped, their errors are returned and collected, see getErrors().
	 *	@param		list<string>	$urls
	 *	@return		array<string, ReadException|ParseException>		Errors by URL
	 */
	public function addUrls( array $urls ): array
	{
		$errors	= [];
		foreach( $this->getReader()->fetchAll( array_combine( $urls, $urls ) ) as $url => $result ){
			try{
				if( $result instanceof ReadException )
					throw $result;
				$this->add( $result->content, $url );
			}
			catch( ReadException|ParseException $e ){
				$errors[$url]	= $e;
			}
		}
		$this->errors	= array_merge( $this->errors, $errors );
		return $errors;
	}

	/**
	 *	Returns items of all feeds, filtered, without duplicates, the newest first.
	 *	Items without date are placed last. Items with equal date keep their order.
	 *	@param		int			$limit		Maximum number of items, 0 for all
	 *	@return		list<Item>
	 */
	public function combine( int $limit = 0 ): array
	{
		$entries	= [];
		foreach( $this->channels as ['channel' => $channel, 'url' => $url] )
			foreach( $channel->getItems() as $item )
				if( $this->accepts( $item, $channel ) )
					$entries[]	= [$item, $channel, $url];
		usort( $entries, static fn( array $a, array $b ): int =>
			( $b[0]->getPubDate()?->getTimestamp() ?? PHP_INT_MIN ) <=> ( $a[0]->getPubDate()?->getTimestamp() ?? PHP_INT_MIN )
		);

		$items	= [];
		$seen	= [];
		foreach( $entries as [$item, $channel, $url] ){
			$key	= $this->unique ? self::getIdentity( $item ) : NULL;
			if( NULL !== $key ){
				if( isset( $seen[$key] ) )
					continue;
				$seen[$key]	= TRUE;
			}
			$items[]	= $this->addSource ? self::withSource( $item, $channel, $url ) : $item;
			if( $limit > 0 && count( $items ) >= $limit )
				break;
		}
		return $items;
	}

	/**
	 *	Returns all added channels.
	 *	@return		list<Channel>
	 */
	public function getChannels(): array
	{
		return array_map( static fn( array $entry ): Channel => $entry['channel'], $this->channels );
	}

	/**
	 *	Returns errors of all calls of addUrls().
	 *	@return		array<string, ReadException|ParseException>		Errors by URL
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 *	Renders combined items into given channel.
	 *	The given channel is not modified, items are added to a copy of it.
	 *	@param		Channel		$channel	Target channel with title, link and description
	 *	@param		int			$limit		Maximum number of items, 0 for all
	 *	@return		string
	 *	@throws		RenderException
	 */
	public function render( Channel $channel, int $limit = 0 ): string
	{
		$target	= clone $channel;
		foreach( $this->combine( $limit ) as $item )
			$target->addItem( $item );
		return Renderer::render( $target, FALSE, $this->extensions );
	}

	/**
	 *	Enables or disables adding <source> to items referencing their original feed.
	 *	@param		bool		$addSource	Flag: add <source> to items, if URL of their feed is known (default: on)
	 *	@return		static
	 */
	public function setAddSource( bool $addSource ): static
	{
		$this->addSource	= $addSource;
		return $this;
	}

	/**
	 *	Enables or disables removing duplicates by GUID or link.
	 *	@param		bool		$unique		Flag: remove duplicates by GUID or link (default: on)
	 *	@return		static
	 */
	public function setUnique( bool $unique ): static
	{
		$this->unique	= $unique;
		return $this;
	}

	/**
	 *	Indicates whether item is accepted by all filters.
	 *	@param		Item		$item		Item model
	 *	@param		Channel		$channel		Channel model
	 *	@return		bool
	 */
	protected function accepts( Item $item, Channel $channel ): bool
	{
		foreach( $this->filters as $filter )
			if( !$filter( $item, $channel ) )
				return FALSE;
		return TRUE;
	}

	/**
	 *	Returns identity of item for duplicate detection: GUID, otherwise link, otherwise NULL.
	 *	@param		Item		$item		Item model
	 *	@return		?string
	 */
	protected static function getIdentity( Item $item ): ?string
	{
		if( NULL !== $item->getGuid() )
			return 'guid:'.$item->getGuid()->value;
		if( Dom::isFilled( $item->getLink() ) )
			return 'link:'.$item->getLink();
		return NULL;
	}

	/**
	 *	Returns URL reader, created with default settings if not given on construction.
	 *	@return		UrlReader
	 */
	protected function getReader(): UrlReader
	{
		return $this->reader ??= new UrlReader();
	}

	/**
	 *	Returns copy of item with source referencing its original feed, if the URL of the feed is known
	 *	(given on adding or by atom:link rel="self") and the item has no source yet.
	 *	@param		Item		$item		Item model
	 *	@param		Channel		$channel		Channel model
	 *	@param		?string		$url		URL
	 *	@return		Item
	 */
	protected static function withSource( Item $item, Channel $channel, ?string $url ): Item
	{
		$url	??= $channel->getExtension( AtomData::class )?->getSelfUrl();
		if( NULL !== $item->getSource() || NULL === $url )
			return $item;
		return ( clone $item )->setSource( new Source( $channel->getTitle(), $url ) );
	}
}
