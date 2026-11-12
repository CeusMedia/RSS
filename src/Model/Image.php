<?php
declare( strict_types=1 );

/**
 *	Data model of an RSS channel image.
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

use InvalidArgumentException;

/**
 *	Data model of an RSS channel image.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class Image
{
	/**	Maximum width of image in pixels, by specification */
	public const int MAX_WIDTH		= 144;
	/**	Maximum height of image in pixels, by specification */
	public const int MAX_HEIGHT		= 400;

	/** @var string URL of the image file */
	protected string $url;
	/** @var ?string Text for the title attribute of the link */
	protected ?string $description	= NULL;
	/** @var ?int Height in pixels */
	protected ?int $height			= NULL;
	/** @var ?string URL the image links to, typically the website of the channel */
	protected ?string $link			= NULL;
	/** @var ?string Alternative text of the image */
	protected ?string $title		= NULL;
	/** @var ?int Width in pixels */
	protected ?int $width			= NULL;

	/**
	 *	Creates image.
	 *	@param		string		$url		URL of GIF, JPEG or PNG image
	 *	@param		?string		$title		Alternative text, defaults to channel title on rendering
	 *	@param		?string		$link		Link target, defaults to channel link on rendering
	 *	@throws		InvalidArgumentException	if URL is empty
	 */
	public function __construct( string $url, ?string $title = NULL, ?string $link = NULL )
	{
		$this->setUrl( $url );
		$this->title	= $title;
		$this->link		= $link;
	}

	/**
	 *	Returns text for the title attribute of the link.
	 *	@return		?string
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 *	Returns height in pixels.
	 *	@return		?int
	 */
	public function getHeight(): ?int
	{
		return $this->height;
	}

	/**
	 *	Returns URL the image links to, typically the website of the channel.
	 *	@return		?string
	 */
	public function getLink(): ?string
	{
		return $this->link;
	}

	/**
	 *	Returns alternative text of the image.
	 *	@return		?string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 *	Returns URL of the image file.
	 *	@return		string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 *	Returns width in pixels.
	 *	@return		?int
	 */
	public function getWidth(): ?int
	{
		return $this->width;
	}

	/**
	 *	Sets text for the title attribute of the link.
	 *	@param		?string		$description		Text for the title attribute of the link
	 *	@return		static
	 */
	public function setDescription( ?string $description ): static
	{
		$this->description	= $description;
		return $this;
	}

	/**
	 *	Sets height in pixels.
	 *	@param		?int		$height		Height in pixels (1-400), NULL for default (31)
	 *	@return		static
	 *	@throws		InvalidArgumentException	if height is out of range
	 */
	public function setHeight( ?int $height ): static
	{
		if( NULL !== $height && ( $height < 1 || $height > self::MAX_HEIGHT ) )
			throw new InvalidArgumentException( 'Image height must be between 1 and '.self::MAX_HEIGHT );
		$this->height	= $height;
		return $this;
	}

	/**
	 *	Sets URL the image links to, typically the website of the channel.
	 *	@param		?string		$link		URL the image links to, typically the website of the channel
	 *	@return		static
	 */
	public function setLink( ?string $link ): static
	{
		$this->link	= $link;
		return $this;
	}

	/**
	 *	Sets alternative text of the image.
	 *	@param		?string		$title		Alternative text of the image
	 *	@return		static
	 */
	public function setTitle( ?string $title ): static
	{
		$this->title	= $title;
		return $this;
	}

	/**
	 *	Sets URL of the image file.
	 *	@param		string		$url		URL of the image file
	 *	@return		static
	 *	@throws		InvalidArgumentException	if URL is empty
	 */
	public function setUrl( string $url ): static
	{
		if( '' === trim( $url ) )
			throw new InvalidArgumentException( 'Image URL cannot be empty' );
		$this->url	= $url;
		return $this;
	}

	/**
	 *	Sets width in pixels.
	 *	@param		?int		$width		Width in pixels (1-144), NULL for default (88)
	 *	@return		static
	 *	@throws		InvalidArgumentException	if width is out of range
	 */
	public function setWidth( ?int $width ): static
	{
		if( NULL !== $width && ( $width < 1 || $width > self::MAX_WIDTH ) )
			throw new InvalidArgumentException( 'Image width must be between 1 and '.self::MAX_WIDTH );
		$this->width	= $width;
		return $this;
	}
}
