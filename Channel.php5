<?php
/**
 *	Channel object for RSS structure.
 *	@copyright	2012 iamkriss.net
 *	@author		kriss <me@iamkriss.net> 
 *	@since		2012-08-20
 *	@version	1.0
 *	@license	Creative Commons SA-BY-NC 3.0
 */
class CMM_RSS_Channel{
	
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
	
	public function addItem( CMM_RSS_Item $item ){
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