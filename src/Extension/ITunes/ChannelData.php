<?php
declare( strict_types=1 );

/**
 *	Podcast data of a channel for Apple Podcasts and other directories (itunes:*).
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
namespace CeusMedia\RSS\Extension\ITunes;

use CeusMedia\RSS\Model\Person;
use InvalidArgumentException;

/**
 *	Podcast data of a channel for Apple Podcasts and other directories (itunes:*).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class ChannelData
{
	/**	Valid types of podcasts */
	public const array TYPES	= ['episodic', 'serial'];

	/** @var ?string Author of the podcast, shown in directories */
	protected ?string $author		= NULL;
	/** @var bool Flag: podcast is hidden in Apple Podcasts */
	protected bool $block			= FALSE;
	/** @var list<Category> Apple Podcasts categories of the podcast */
	protected array $categories		= [];
	/** @var bool Flag: no more episodes will be published */
	protected bool $complete		= FALSE;
	/** @var ?bool Flag: podcast contains explicit content, NULL if not specified */
	protected ?bool $explicit		= NULL;
	/** @var ?string URL of the artwork of the podcast */
	protected ?string $image		= NULL;
	/** @var ?string New URL of the feed, after moving it */
	protected ?string $newFeedUrl	= NULL;
	/** @var ?Person Owner of the podcast, contact for directories */
	protected ?Person $owner		= NULL;
	/** @var ?string Title of the podcast, if different from the channel title */
	protected ?string $title		= NULL;
	/** @var ?string Type of the podcast: episodic or serial */
	protected ?string $type			= NULL;

	/**
	 *	Adds an Apple Podcasts category.
	 *	@param		Category		$category		Apple Podcasts category
	 *	@return		static
	 */
	public function addCategory( Category $category ): static
	{
		$this->categories[]	= $category;
		return $this;
	}

	/**
	 *	Returns author of the podcast, shown in directories.
	 *	@return		?string
	 */
	public function getAuthor(): ?string
	{
		return $this->author;
	}

	/**
	 *	Returns Apple Podcasts categories of the podcast.
	 *	@return		list<Category>
	 */
	public function getCategories(): array
	{
		return $this->categories;
	}

	/**
	 *	Returns flag: podcast contains explicit content, NULL if not specified.
	 *	@return		?bool
	 */
	public function getExplicit(): ?bool
	{
		return $this->explicit;
	}

	/**
	 *	Returns URL of the artwork of the podcast.
	 *	@return		?string
	 */
	public function getImage(): ?string
	{
		return $this->image;
	}

	/**
	 *	Returns new URL of the feed, after moving it.
	 *	@return		?string
	 */
	public function getNewFeedUrl(): ?string
	{
		return $this->newFeedUrl;
	}

	/**
	 *	Returns owner of the podcast, contact for directories.
	 *	@return		?Person
	 */
	public function getOwner(): ?Person
	{
		return $this->owner;
	}

	/**
	 *	Returns title of the podcast, if different from the channel title.
	 *	@return		?string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 *	Returns type of the podcast: episodic or serial.
	 *	@return		?string
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 *	Returns flag: podcast is hidden in Apple Podcasts.
	 *	@return		bool
	 */
	public function isBlocked(): bool
	{
		return $this->block;
	}

	/**
	 *	Returns flag: no more episodes will be published.
	 *	@return		bool
	 */
	public function isComplete(): bool
	{
		return $this->complete;
	}

	/**
	 *	Sets author of the podcast, shown in directories.
	 *	@param		?string		$author		Author of the podcast, shown in directories
	 *	@return		static
	 */
	public function setAuthor( ?string $author ): static
	{
		$this->author	= $author;
		return $this;
	}

	/**
	 *	Sets flag: podcast is hidden in Apple Podcasts.
	 *	@param		bool		$block		Flag: hide podcast in Apple Podcasts
	 *	@return		static
	 */
	public function setBlock( bool $block ): static
	{
		$this->block	= $block;
		return $this;
	}

	/**
	 *	Sets Apple Podcasts categories of the podcast.
	 *	@param		list<Category>	$categories		Apple Podcasts categories of the podcast
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
	 *	Sets flag: no more episodes will be published.
	 *	@param		bool		$complete		Flag: no more episodes will be published
	 *	@return		static
	 */
	public function setComplete( bool $complete ): static
	{
		$this->complete	= $complete;
		return $this;
	}

	/**
	 *	Sets flag: podcast contains explicit content, NULL if not specified.
	 *	@param		?bool		$explicit		Flag: podcast contains explicit content, NULL if not specified
	 *	@return		static
	 */
	public function setExplicit( ?bool $explicit ): static
	{
		$this->explicit	= $explicit;
		return $this;
	}

	/**
	 *	Sets URL of the artwork of the podcast.
	 *	@param		?string		$url		URL of artwork, JPEG or PNG, 1400×1400 to 3000×3000 pixels
	 *	@return		static
	 */
	public function setImage( ?string $url ): static
	{
		$this->image	= $url;
		return $this;
	}

	/**
	 *	Sets new URL of the feed, after moving it.
	 *	@param		?string		$url		New URL of the feed, after moving it
	 *	@return		static
	 */
	public function setNewFeedUrl( ?string $url ): static
	{
		$this->newFeedUrl	= $url;
		return $this;
	}

	/**
	 *	Sets owner of the podcast, contact for directories.
	 *	@param		?Person		$owner		Contact of podcast owner, not shown publicly
	 *	@return		static
	 */
	public function setOwner( ?Person $owner ): static
	{
		$this->owner	= $owner;
		return $this;
	}

	/**
	 *	Sets title of the podcast, if different from the channel title.
	 *	@param		?string		$title		Title of the podcast, if different from the channel title
	 *	@return		static
	 */
	public function setTitle( ?string $title ): static
	{
		$this->title	= $title;
		return $this;
	}

	/**
	 *	Sets type of the podcast: episodic or serial.
	 *	@param		?string		$type		"episodic" (newest first) or "serial" (oldest first)
	 *	@return		static
	 *	@throws		InvalidArgumentException	if type is invalid
	 */
	public function setType( ?string $type ): static
	{
		if( NULL !== $type && !in_array( $type, self::TYPES, TRUE ) )
			throw new InvalidArgumentException( 'iTunes type must be one of: '.implode( ', ', self::TYPES ) );
		$this->type	= $type;
		return $this;
	}
}
