<?php
/**
 *	...
 *
 *	Copyright (c) 2012-2015 Christian Würker / {@link https://ceusmedia.de/ Ceus Media}
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
class Item
{
	protected array $author			= [];
	protected ?string $category		= NULL;
	protected ?string $content		= NULL;
	protected ?int $date			= NULL;
	protected ?string $link			= NULL;
	protected array $guid			= [];
	protected array $source			= [];
	protected ?string $title		= NULL;

	public function getAuthor(): array
	{
		return $this->author;
	}

	public function getCategory(): ?string
	{
		return $this->category;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function getDate(): ?int
	{
		return $this->date;
	}

	public function getId(): array
	{
		return $this->guid;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function getSource(): array
	{
		return $this->source;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setAuthor( string $email, ?string $name = NULL ): self
	{
		$this->author	= array( $email, $name );
		return $this;
	}

	public function setCategory( string $category ): self
	{
		$this->category	= $category;
		return $this;
	}

	public function setContent( string $content ): self
	{
		$this->content	= $content;
		return $this;
	}

	public function setDate( ?int $date ): self
	{
		$this->date		= $date;
		return $this;
	}

	public function setId( string $uri, bool $isPermaLink = FALSE ): self
	{
		$this->guid	= [$uri, $isPermaLink];
		return $this;
	}

	public function setLink( string $link ): self
	{
		$this->link		= $link;
		return $this;
	}

	public function setSource( string $label, ?string $uri ): self
	{
		$this->source	= [$label, $uri];
		return $this;
	}

	public function setTitle( string $title ): self
	{
		$this->title	= $title;
		return $this;
	}
}
