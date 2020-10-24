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
 *	@copyright		2012-2020 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
namespace CeusMedia\RSS\Model;

use CeusMedia\RSS\Model\Image;
use CeusMedia\RSS\Model\Item;

/**
 *	...
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2020 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Channel
{
	protected $admin		= array();
	protected $category		= NULL;
	protected $cloud		= NULL;
	protected $copyright	= NULL;
	protected $date			= NULL;
	protected $description	= NULL;
	protected $docs			= "http://www.rssboard.org/rss-specification";
	protected $generator	= NULL;
	protected $image		= array();
	protected $items		= array();
	protected $language		= NULL;
	protected $link			= NULL;
	protected $manager		= array();
	protected $title		= NULL;

	public function addItem( Item $item )
	{
		$this->items[]	= $item;
	}

	public function getAdmin()
	{
		return $this->admin;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getCloud()
	{
		return $this->cloud;
	}

	public function getCopyright()
	{
		return $this->copyright;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getDocs()
	{
		return $this->docs;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getGenerator()
	{
		return $this->generator;
	}

	public function getImage()
	{
		return $this->image;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getLink()
	{
		return $this->link;
	}

	public function getManager()
	{
		return $this->manager;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setAdmin( $email, $name = NULL ): self
	{
		$this->admin	= array( $email, $name );
		return $this;
	}

	public function setCategory( $category ): self
	{
		$this->category		= $category;
		return $this;
	}

	public function setCloud( $parameters ): self
	{
		$this->cloud	= $parameters;
		return $this;
	}

	public function setCopyright( $copyright ): self
	{
		$this->copyright		= $copyright;
		return $this;
	}

	public function setDate( $date ): self
	{
		$this->date	= $date;
		return $this;
	}

	public function setDocs( $url ): self
	{
		$this->docs	= $url;
		return $this;
	}

	public function setDescription( $description ): self
	{
		$this->description	= $description;
		return $this;
	}

	public function setGenerator( $generator ): self
	{
		$this->generator	= $generator;
		return $this;
	}

	public function setImage( Image $image ): self
	{
		$this->image	= $image;
		return $this;
	}

	public function setLanguage( $language ): self
	{
		$this->language	= $language;
		return $this;
	}

	public function setLink( $link ): self
	{
		$this->link	= $link;
		return $this;
	}

	public function setManager( $email, $name = NULL ): self
	{
		$this->manager	= array( $email, $name );
		return $this;
	}

	public function setTitle( $title ): self
	{
		$this->title	= $title;
		return $this;
	}
}
