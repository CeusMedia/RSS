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
namespace CeusMedia\RSS\Model;

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
class Channel
{
	protected array $admin			= [];
	protected ?string $category		= NULL;
	protected array $cloud			= [];
	protected ?string $copyright	= NULL;
	protected ?int $date			= NULL;
	protected ?string $description	= NULL;
	protected string $docs			= "https://www.rssboard.org/rss-specification";
	protected ?string $generator	= NULL;
	protected ?Image $image			= NULL;
	protected array $items			= [];
	protected ?string $language		= NULL;
	protected ?string $link			= NULL;
	protected array $manager		= [];
	protected ?string $title		= NULL;

	public function addItem( Item $item ): self
	{
		$this->items[]	= $item;
		return $this;
	}

	public function getAdmin(): array
	{
		return $this->admin;
	}

	public function getCategory(): ?string
	{
		return $this->category;
	}

	public function getCloud(): array
	{
		return $this->cloud;
	}

	public function getCopyright(): ?string
	{
		return $this->copyright;
	}

	public function getDate(): ?int
	{
		return $this->date;
	}

	public function getDocs(): string
	{
		return $this->docs;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function getGenerator(): ?string
	{
		return $this->generator;
	}

	public function getImage(): ?Image
	{
		return $this->image;
	}

	public function getItems(): array
	{
		return $this->items;
	}

	public function getLanguage(): ?string
	{
		return $this->language;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function getManager(): array
	{
		return $this->manager;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setAdmin( string $email, ?string $name = NULL ): self
	{
		$this->admin	= array( $email, $name );
		return $this;
	}

	public function setCategory( string $category ): self
	{
		$this->category		= $category;
		return $this;
	}

	public function setCloud( array $parameters ): self
	{
		$this->cloud	= $parameters;
		return $this;
	}

	public function setCopyright( string $copyright ): self
	{
		$this->copyright		= $copyright;
		return $this;
	}

	public function setDate( ?int $date ): self
	{
		$this->date	= $date;
		return $this;
	}

	public function setDocs( string $url ): self
	{
		$this->docs	= $url;
		return $this;
	}

	public function setDescription( string $description ): self
	{
		$this->description	= $description;
		return $this;
	}

	public function setGenerator( string $generator ): self
	{
		$this->generator	= $generator;
		return $this;
	}

	public function setImage( Image $image ): self
	{
		$this->image	= $image;
		return $this;
	}

	public function setLanguage( ?string $language ): self
	{
		$this->language	= $language;
		return $this;
	}

	public function setLink( ?string $link ): self
	{
		$this->link	= $link;
		return $this;
	}

	public function setManager( string $email, ?string $name = NULL ): self
	{
		$this->manager	= array( $email, $name );
		return $this;
	}

	public function setTitle( ?string $title ): self
	{
		$this->title	= $title;
		return $this;
	}
}
