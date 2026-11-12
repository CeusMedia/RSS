<?php
declare( strict_types=1 );

/**
 *	Data model of an RSS channel.
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
use InvalidArgumentException;

/**
 *	Data model of an RSS channel.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Channel
{
	use ExtensionDataTrait;

	/**	Default URL of format documentation */
	public const string DEFAULT_DOCS	= 'https://www.rssboard.org/rss-specification';

	/**	Valid day names for skipDays */
	public const array DAYS			= ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	/** @var list<Category> Categories of the channel */
	protected array $categories						= [];
	/** @var ?Cloud Cloud (rssCloud) settings for notifications about updates */
	protected ?Cloud $cloud							= NULL;
	/** @var ?string Copyright notice of the content */
	protected ?string $copyright					= NULL;
	/** @var string Description of the channel */
	protected string $description;
	/** @var ?string URL of the documentation of the RSS format */
	protected ?string $docs							= self::DEFAULT_DOCS;
	/** @var ?string Name of the program used to generate the feed */
	protected ?string $generator					= NULL;
	/** @var ?Image Image of the channel */
	protected ?Image $image							= NULL;
	/** @var list<Item> Items of the channel */
	protected array $items							= [];
	/** @var ?string Language of the channel */
	protected ?string $language						= NULL;
	/** @var ?DateTimeImmutable Date of the last change of the content */
	protected ?DateTimeImmutable $lastBuildDate		= NULL;
	/** @var string URL of the website belonging to the channel */
	protected string $link;
	/** @var ?Person Managing editor, responsible for the content */
	protected ?Person $managingEditor				= NULL;
	/** @var ?DateTimeImmutable Publication date of the content */
	protected ?DateTimeImmutable $pubDate			= NULL;
	/** @var ?string PICS rating of the channel */
	protected ?string $rating						= NULL;
	/** @var list<string> Days on which aggregators may skip reading the feed */
	protected array $skipDays						= [];
	/** @var list<int> Hours (GMT) in which aggregators may skip reading the feed */
	protected array $skipHours						= [];
	/** @var ?TextInput Text input box of the channel */
	protected ?TextInput $textInput					= NULL;
	/** @var string Title of the channel */
	protected string $title;
	/** @var ?int Time to live: minutes the channel may be cached */
	protected ?int $ttl								= NULL;
	/** @var ?Person Web master, responsible for technical issues */
	protected ?Person $webMaster					= NULL;

	/**
	 *	Title, link and description are required for rendering.
	 *	@param		string		$title		Title
	 *	@param		string		$link		Link
	 *	@param		string		$description		Description
	 */
	public function __construct( string $title = '', string $link = '', string $description = '' )
	{
		$this->title		= $title;
		$this->link			= $link;
		$this->description	= $description;
	}

	/**
	 *	Adds a category of the channel.
	 *	@param		Category|string		$category		Category object or name
	 *	@return		static
	 */
	public function addCategory( Category|string $category ): static
	{
		$this->categories[]	= is_string( $category ) ? new Category( $category ) : $category;
		return $this;
	}

	/**
	 *	Adds an item to the channel.
	 *	@param		Item		$item		Item to the channel
	 *	@return		static
	 */
	public function addItem( Item $item ): static
	{
		$this->items[]	= $item;
		return $this;
	}

	/**
	 *	Returns categories of the channel.
	 *	@return		list<Category>
	 */
	public function getCategories(): array
	{
		return $this->categories;
	}

	/**
	 *	Returns cloud (rssCloud) settings for notifications about updates.
	 *	@return		?Cloud
	 */
	public function getCloud(): ?Cloud
	{
		return $this->cloud;
	}

	/**
	 *	Returns copyright notice of the content.
	 *	@return		?string
	 */
	public function getCopyright(): ?string
	{
		return $this->copyright;
	}

	/**
	 *	Returns description of the channel.
	 *	@return		string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 *	Returns URL of the documentation of the RSS format.
	 *	@return		?string
	 */
	public function getDocs(): ?string
	{
		return $this->docs;
	}

	/**
	 *	Returns name of the program used to generate the feed.
	 *	@return		?string
	 */
	public function getGenerator(): ?string
	{
		return $this->generator;
	}

	/**
	 *	Returns image of the channel.
	 *	@return		?Image
	 */
	public function getImage(): ?Image
	{
		return $this->image;
	}

	/**
	 *	Returns items of the channel.
	 *	@return		list<Item>
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 *	Returns language of the channel.
	 *	@return		?string
	 */
	public function getLanguage(): ?string
	{
		return $this->language;
	}

	/**
	 *	Returns date of last build. If not set, the renderer will use the current time.
	 *	@return		?DateTimeImmutable
	 */
	public function getLastBuildDate(): ?DateTimeImmutable
	{
		return $this->lastBuildDate;
	}

	/**
	 *	Returns URL of the website belonging to the channel.
	 *	@return		string
	 */
	public function getLink(): string
	{
		return $this->link;
	}

	/**
	 *	Returns managing editor, responsible for the content.
	 *	@return		?Person
	 */
	public function getManagingEditor(): ?Person
	{
		return $this->managingEditor;
	}

	/**
	 *	Returns publication date of the content.
	 *	@return		?DateTimeImmutable
	 */
	public function getPubDate(): ?DateTimeImmutable
	{
		return $this->pubDate;
	}

	/**
	 *	Returns PICS rating of the channel.
	 *	@return		?string
	 */
	public function getRating(): ?string
	{
		return $this->rating;
	}

	/**
	 *	Returns days on which aggregators may skip reading the feed.
	 *	@return		list<string>
	 */
	public function getSkipDays(): array
	{
		return $this->skipDays;
	}

	/**
	 *	Returns hours (GMT) in which aggregators may skip reading the feed.
	 *	@return		list<int>
	 */
	public function getSkipHours(): array
	{
		return $this->skipHours;
	}

	/**
	 *	Returns text input box of the channel.
	 *	@return		?TextInput
	 */
	public function getTextInput(): ?TextInput
	{
		return $this->textInput;
	}

	/**
	 *	Returns title of the channel.
	 *	@return		string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 *	Returns time to live in minutes.
	 *	@return		?int
	 */
	public function getTtl(): ?int
	{
		return $this->ttl;
	}

	/**
	 *	Returns web master, responsible for technical issues.
	 *	@return		?Person
	 */
	public function getWebMaster(): ?Person
	{
		return $this->webMaster;
	}

	/**
	 *	Sets categories of the channel.
	 *	@param		list<Category>		$categories		Categories of the channel
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
	 *	Sets cloud (rssCloud) settings for notifications about updates.
	 *	@param		?Cloud		$cloud		Cloud (rssCloud) settings for notifications about updates
	 *	@return		static
	 */
	public function setCloud( ?Cloud $cloud ): static
	{
		$this->cloud	= $cloud;
		return $this;
	}

	/**
	 *	Sets copyright notice of the content.
	 *	@param		?string		$copyright		Copyright notice of the content
	 *	@return		static
	 */
	public function setCopyright( ?string $copyright ): static
	{
		$this->copyright	= $copyright;
		return $this;
	}

	/**
	 *	Sets description of the channel.
	 *	@param		string		$description		Description of the channel
	 *	@return		static
	 */
	public function setDescription( string $description ): static
	{
		$this->description	= $description;
		return $this;
	}

	/**
	 *	Sets URL of the documentation of the RSS format.
	 *	@param		?string		$url		URL of format documentation, NULL to omit
	 *	@return		static
	 */
	public function setDocs( ?string $url ): static
	{
		$this->docs	= $url;
		return $this;
	}

	/**
	 *	Sets name of the program used to generate the feed.
	 *	@param		?string		$generator		Name of the program used to generate the feed
	 *	@return		static
	 */
	public function setGenerator( ?string $generator ): static
	{
		$this->generator	= $generator;
		return $this;
	}

	/**
	 *	Sets image of the channel.
	 *	@param		?Image		$image		Image of the channel
	 *	@return		static
	 */
	public function setImage( ?Image $image ): static
	{
		$this->image	= $image;
		return $this;
	}

	/**
	 *	Sets items of the channel.
	 *	@param		list<Item>		$items		Items of the channel
	 *	@return		static
	 */
	public function setItems( array $items ): static
	{
		$this->items	= [];
		foreach( $items as $item )
			$this->addItem( $item );
		return $this;
	}

	/**
	 *	Sets language of the channel.
	 *	@param		?string		$language		Language code, like "en-us" or "de"
	 *	@return		static
	 */
	public function setLanguage( ?string $language ): static
	{
		$this->language	= $language;
		return $this;
	}

	/**
	 *	Sets date of the last change of the content.
	 *	@param		?DateTimeInterface		$date		Date of the last change of the content
	 *	@return		static
	 */
	public function setLastBuildDate( ?DateTimeInterface $date ): static
	{
		$this->lastBuildDate	= NULL !== $date ? DateTimeImmutable::createFromInterface( $date ) : NULL;
		return $this;
	}

	/**
	 *	Sets URL of the website belonging to the channel.
	 *	@param		string		$link		URL of the website belonging to the channel
	 *	@return		static
	 */
	public function setLink( string $link ): static
	{
		$this->link	= $link;
		return $this;
	}

	/**
	 *	Sets managing editor, responsible for the content.
	 *	@param		?Person		$person		Managing editor, responsible for the content
	 *	@return		static
	 */
	public function setManagingEditor( ?Person $person ): static
	{
		$this->managingEditor	= $person;
		return $this;
	}

	/**
	 *	Sets publication date of the content.
	 *	@param		?DateTimeInterface		$date		Publication date of the content
	 *	@return		static
	 */
	public function setPubDate( ?DateTimeInterface $date ): static
	{
		$this->pubDate	= NULL !== $date ? DateTimeImmutable::createFromInterface( $date ) : NULL;
		return $this;
	}

	/**
	 *	Sets PICS rating of the channel.
	 *	@param		?string		$rating		PICS rating
	 *	@return		static
	 */
	public function setRating( ?string $rating ): static
	{
		$this->rating	= $rating;
		return $this;
	}

	/**
	 *	Sets days on which aggregators may skip reading the feed.
	 *	@param		list<string>	$days		English day names, like "Saturday"
	 *	@return		static
	 *	@throws		InvalidArgumentException	if a day name is invalid
	 */
	public function setSkipDays( array $days ): static
	{
		$list	= [];
		foreach( $days as $day ){
			$day	= ucfirst( strtolower( trim( $day ) ) );
			if( !in_array( $day, self::DAYS, TRUE ) )
				throw new InvalidArgumentException( 'Invalid skip day: '.$day );
			$list[$day]	= $day;
		}
		$this->skipDays	= array_values( array_intersect( self::DAYS, $list ) );
		return $this;
	}

	/**
	 *	Sets hours (GMT) in which aggregators may skip reading the feed.
	 *	@param		list<int>		$hours		Hours from 0 to 23
	 *	@return		static
	 *	@throws		InvalidArgumentException	if an hour is out of range
	 */
	public function setSkipHours( array $hours ): static
	{
		foreach( $hours as $hour )
			if( $hour < 0 || $hour > 23 )
				throw new InvalidArgumentException( 'Skip hour must be between 0 and 23, '.$hour.' given' );
		$hours	= array_values( array_unique( $hours ) );
		sort( $hours );
		$this->skipHours	= $hours;
		return $this;
	}

	/**
	 *	Sets text input box of the channel.
	 *	@param		?TextInput		$textInput		Text input box of the channel
	 *	@return		static
	 */
	public function setTextInput( ?TextInput $textInput ): static
	{
		$this->textInput	= $textInput;
		return $this;
	}

	/**
	 *	Sets title of the channel.
	 *	@param		string		$title		Title of the channel
	 *	@return		static
	 */
	public function setTitle( string $title ): static
	{
		$this->title	= $title;
		return $this;
	}

	/**
	 *	Sets time to live: minutes the channel may be cached.
	 *	@param		?int		$minutes		Time to live in minutes
	 *	@return		static
	 *	@throws		InvalidArgumentException	if negative
	 */
	public function setTtl( ?int $minutes ): static
	{
		if( NULL !== $minutes && $minutes < 0 )
			throw new InvalidArgumentException( 'TTL cannot be negative' );
		$this->ttl	= $minutes;
		return $this;
	}

	/**
	 *	Sets web master, responsible for technical issues.
	 *	@param		?Person		$person		Web master, responsible for technical issues
	 *	@return		static
	 */
	public function setWebMaster( ?Person $person ): static
	{
		$this->webMaster	= $person;
		return $this;
	}
}
