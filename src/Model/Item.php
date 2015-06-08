<?php
/**
 *	Item object for RSS structure.
 *	@copyright	2012 iamkriss.net
 *	@author		kriss <me@iamkriss.net> 
 *	@since		2012-08-20
 *	@version	1.0
 *	@license	LGPL 3
 */
class CMM_RSS_Item{
	
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