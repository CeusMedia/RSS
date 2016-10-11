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
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
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
 *	@copyright		2012-2015 {@link http://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Channel{

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

	public function addItem( \CeusMedia\RSS\Model\Item $item ){
		$this->items[]	= $item;
	}

	public function getAdmin(){
		return $this->admin;
	}

	public function getCategory(){
		return $this->category;
	}

	public function getCloud(){
		return $this->cloud;
	}

	public function getCopyright(){
		return $this->copyright;
	}

	public function getDate(){
		return $this->date;
	}

	public function getDocs(){
		return $this->docs;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getGenerator(){
		return $this->generator;
	}

	public function getImage(){
		return $this->image;
	}

	public function getItems(){
		return $this->items;
	}

	public function getLanguage(){
		return $this->language;
	}

	public function getLink(){
		return $this->link;
	}

	public function getManager(){
		return $this->manager;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setAdmin( $email, $name = NULL ){
		$this->admin	= array( $email, $name );
	}

	public function setCategory( $category ){
		$this->category		= $category;
	}

	public function setCloud( $parameters ){
		$this->cloud	= $parameters;
	}

	public function setCopyright( $copyright ){
		$this->copyright		= $copyright;
	}

	public function setDate( $date ){
		$this->date	= $date;
	}

	public function setDocs( $url ){
		$this->docs	= $url;
	}

	public function setDescription( $description ){
		$this->description	= $description;
	}

	public function setGenerator( $generator ){
		$this->generator	= $generator;
	}

	public function setImage( $url, $title, $link, $description = NULL, $width = NULL, $height = NULL ){
		$this->image	= array( $url, $title, $link, $description, $width, $height );
	}

	public function setLanguage( $language){
		$this->language	= $language;
	}

	public function setLink( $link ){
		$this->link	= $link;
	}

	public function setManager( $email, $name = NULL ){
		$this->manager	= array( $email, $name );
	}

	public function setTitle( $title ){
		$this->title	= $title;
	}
}
?>
