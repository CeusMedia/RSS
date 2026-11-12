<?php
declare( strict_types=1 );

/**
 *	Data model of an RSS item.
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

use DateTimeImmutable;
use DateTimeInterface;

/**
 *	Data model of an RSS item.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Item
{
	use ExtensionDataTrait;

	/** @var ?Person Author of the item */
	protected ?Person $author				= NULL;
	/** @var list<Category> Categories of the item */
	protected array $categories				= [];
	/** @var ?string URL of the page with comments on the item */
	protected ?string $comments				= NULL;
	/** @var ?string Synopsis of the item, may contain HTML */
	protected ?string $description			= NULL;
	/** @var ?Enclosure Media object attached to the item, like an audio file of a podcast */
	protected ?Enclosure $enclosure			= NULL;
	/** @var ?Guid Globally unique identifier of the item */
	protected ?Guid $guid					= NULL;
	/** @var ?string URL of the item */
	protected ?string $link					= NULL;
	/** @var ?DateTimeImmutable Publication date of the item */
	protected ?DateTimeImmutable $pubDate	= NULL;
	/** @var ?Source Channel the item came from */
	protected ?Source $source				= NULL;
	/** @var ?string Title of the item */
	protected ?string $title				= NULL;

	/**
	 *	At least one of title or description is required for rendering.
	 *	@param		?string		$title		Title
	 *	@param		?string		$link		Link
	 *	@param		?string		$description		Description
	 */
	public function __construct( ?string $title = NULL, ?string $link = NULL, ?string $description = NULL )
	{
		$this->title		= $title;
		$this->link			= $link;
		$this->description	= $description;
	}

	/**
	 *	Adds a category of the item.
	 *	@param		Category|string		$category		Category object or name
	 *	@return		static
	 */
	public function addCategory( Category|string $category ): static
	{
		$this->categories[]	= is_string( $category ) ? new Category( $category ) : $category;
		return $this;
	}

	/**
	 *	Returns author of the item.
	 *	@return		?Person
	 */
	public function getAuthor(): ?Person
	{
		return $this->author;
	}

	/**
	 *	Returns categories of the item.
	 *	@return		list<Category>
	 */
	public function getCategories(): array
	{
		return $this->categories;
	}

	/**
	 *	Returns URL of the page with comments on the item.
	 *	@return		?string
	 */
	public function getComments(): ?string
	{
		return $this->comments;
	}

	/**
	 *	Returns synopsis of the item, may contain HTML.
	 *	@return		?string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 *	Returns media object attached to the item, like an audio file of a podcast.
	 *	@return		?Enclosure
	 */
	public function getEnclosure(): ?Enclosure
	{
		return $this->enclosure;
	}

	/**
	 *	Returns globally unique identifier of the item.
	 *	@return		?Guid
	 */
	public function getGuid(): ?Guid
	{
		return $this->guid;
	}

	/**
	 *	Returns URL of the item.
	 *	@return		?string
	 */
	public function getLink(): ?string
	{
		return $this->link;
	}

	/**
	 *	Returns publication date of the item.
	 *	@return		?DateTimeImmutable
	 */
	public function getPubDate(): ?DateTimeImmutable
	{
		return $this->pubDate;
	}

	/**
	 *	Returns channel the item came from.
	 *	@return		?Source
	 */
	public function getSource(): ?Source
	{
		return $this->source;
	}

	/**
	 *	Returns title of the item.
	 *	@return		?string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 *	Sets author of the item.
	 *	@param		?Person		$author		Author of the item
	 *	@return		static
	 */
	public function setAuthor( ?Person $author ): static
	{
		$this->author	= $author;
		return $this;
	}

	/**
	 *	Sets categories of the item.
	 *	@param		list<Category>		$categories		Categories of the item
	 *	@return		static
	 */
	public function setCategories( array $categories ): static
	{
		$this->categories	= [];
		foreach( $categories as $category )
			$this->addCategory( $category );
		return $this;
	}

	/**
	 *	Sets URL of the page with comments on the item.
	 *	@param		?string		$comments		URL of comments page
	 *	@return		static
	 */
	public function setComments( ?string $comments ): static
	{
		$this->comments	= $comments;
		return $this;
	}

	/**
	 *	Sets synopsis of the item, may contain HTML.
	 *	@param		?string		$description	Item synopsis, may contain HTML (will be escaped on rendering)
	 *	@return		static
	 */
	public function setDescription( ?string $description ): static
	{
		$this->description	= $description;
		return $this;
	}

	/**
	 *	Sets media object attached to the item, like an audio file of a podcast.
	 *	@param		?Enclosure		$enclosure		Media object attached to the item, like an audio file of a podcast
	 *	@return		static
	 */
	public function setEnclosure( ?Enclosure $enclosure ): static
	{
		$this->enclosure	= $enclosure;
		return $this;
	}

	/**
	 *	Sets globally unique identifier of the item.
	 *	@param		?Guid		$guid		Globally unique identifier of the item
	 *	@return		static
	 */
	public function setGuid( ?Guid $guid ): static
	{
		$this->guid	= $guid;
		return $this;
	}

	/**
	 *	Sets URL of the item.
	 *	@param		?string		$link		URL of the item
	 *	@return		static
	 */
	public function setLink( ?string $link ): static
	{
		$this->link	= $link;
		return $this;
	}

	/**
	 *	Sets publication date of the item.
	 *	@param		?DateTimeInterface		$pubDate		Publication date of the item
	 *	@return		static
	 */
	public function setPubDate( ?DateTimeInterface $pubDate ): static
	{
		$this->pubDate	= NULL !== $pubDate ? DateTimeImmutable::createFromInterface( $pubDate ) : NULL;
		return $this;
	}

	/**
	 *	Sets channel the item came from.
	 *	@param		?Source		$source		Channel the item came from
	 *	@return		static
	 */
	public function setSource( ?Source $source ): static
	{
		$this->source	= $source;
		return $this;
	}

	/**
	 *	Sets title of the item.
	 *	@param		?string		$title		Title of the item
	 *	@return		static
	 */
	public function setTitle( ?string $title ): static
	{
		$this->title	= $title;
		return $this;
	}
}
