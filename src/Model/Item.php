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
namespace CeusMedia\Rss\Model;
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
class Item{

	protected $author		= array();
	protected $category		= NULL;
	protected $content		= NULL;
	protected $date			= NULL;
	protected $link			= NULL;
	protected $guid			= array();
	protected $source		= array();
	protected $title		= NULL;

	public function getAuthor(){
		return $this->author;
	}

	public function getCategory(){
		return $this->category;
	}

	public function getContent(){
		return $this->content;
	}

	public function getDate(){
		return $this->date;
	}

	public function getId(){
		return $this->guid;
	}

	public function getLink(){
		return $this->link;
	}

	public function getSource(){
		return $this->source;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setAuthor( $email, $name = NULL ){
		$this->author	= array( $email, $name );
	}

	public function setCategory( $category ){
		$this->category	= $category;
	}

	public function setContent( $content ){
		$this->content	= $content;
	}

	public function setDate( $date ){
		$this->date		= $date;
	}

	public function setId( $uri, $isPermaLink = FALSE ){
		$this->guid	= array( $uri, $isPermaLink );
	}

	public function setLink( $link ){
		$this->link		= $link;
	}

	public function setSource( $uri, $label ){
		$this->source	= array( $uri, $label );
	}

	public function setTitle( $title ){
		$this->title	= $title;
	}
}
?>
