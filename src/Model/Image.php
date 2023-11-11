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
 */class Image
{
	protected ?string $url			= NULL;
	protected ?string $title		= NULL;
	protected ?string $link			= NULL;
	protected ?string $description	= NULL;
	protected ?int $width			= NULL;
	protected ?int $height			= NULL;

	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function getLink(): ?string
	{
		return $this->link;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function getWidth(): ?int
	{
		return $this->width;
	}

	public function getHeight(): ?int
	{
		return $this->height;
	}

	public function setUrl( string $url ): self
	{
		$this->url	= $url;
		return $this;
	}

	public function setTitle( ?string $title ): self
	{
		$this->title	= $title;
		return $this;
	}

	public function setLink( ?string $link ): self
	{
		$this->link	= $link;
		return $this;
	}

	public function setDescription( ?string $description ): self
	{
		$this->description	= $description;
		return $this;
	}

	public function setWidth( ?int $width ): self
	{
		$this->width	= $width;
		return $this;
	}

	public function setHeight( ?int $height ): self
	{
		$this->height	= $height;
		return $this;
	}
}
