<?php
declare( strict_types=1 );

/**
 *	Podcast data of an episode for Apple Podcasts and other directories (itunes:*).
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
namespace CeusMedia\RSS\Extension\ITunes;

use InvalidArgumentException;

/**
 *	Podcast data of an episode for Apple Podcasts and other directories (itunes:*).
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
class ItemData
{
	/**	Valid types of episodes */
	public const array EPISODE_TYPES	= ['full', 'trailer', 'bonus'];

	/** @var bool Flag: episode is hidden in Apple Podcasts */
	protected bool $block				= FALSE;
	/** @var ?int Duration of the episode in seconds */
	protected ?int $duration			= NULL;
	/** @var ?int Number of the episode */
	protected ?int $episode				= NULL;
	/** @var ?string Type of the episode: full, trailer or bonus */
	protected ?string $episodeType		= NULL;
	/** @var ?bool Flag: episode contains explicit content, NULL if not specified */
	protected ?bool $explicit			= NULL;
	/** @var ?string URL of the artwork of the episode */
	protected ?string $image			= NULL;
	/** @var ?int Number of the season */
	protected ?int $season				= NULL;
	/** @var ?string Title of the episode, without episode or season number */
	protected ?string $title			= NULL;

	/**
	 *	Returns duration in seconds.
	 *	@return		?int
	 */
	public function getDuration(): ?int
	{
		return $this->duration;
	}

	/**
	 *	Returns number of the episode.
	 *	@return		?int
	 */
	public function getEpisode(): ?int
	{
		return $this->episode;
	}

	/**
	 *	Returns type of the episode: full, trailer or bonus.
	 *	@return		?string
	 */
	public function getEpisodeType(): ?string
	{
		return $this->episodeType;
	}

	/**
	 *	Returns flag: episode contains explicit content, NULL if not specified.
	 *	@return		?bool
	 */
	public function getExplicit(): ?bool
	{
		return $this->explicit;
	}

	/**
	 *	Returns URL of the artwork of the episode.
	 *	@return		?string
	 */
	public function getImage(): ?string
	{
		return $this->image;
	}

	/**
	 *	Returns number of the season.
	 *	@return		?int
	 */
	public function getSeason(): ?int
	{
		return $this->season;
	}

	/**
	 *	Returns title of the episode, without episode or season number.
	 *	@return		?string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 *	Returns flag: episode is hidden in Apple Podcasts.
	 *	@return		bool
	 */
	public function isBlocked(): bool
	{
		return $this->block;
	}

	/**
	 *	Sets flag: episode is hidden in Apple Podcasts.
	 *	@param		bool		$block		Flag: episode is hidden in Apple Podcasts
	 *	@return		static
	 */
	public function setBlock( bool $block ): static
	{
		$this->block	= $block;
		return $this;
	}

	/**
	 *	Sets duration of the episode in seconds.
	 *	@param		?int		$seconds		Duration in seconds
	 *	@return		static
	 *	@throws		InvalidArgumentException	if negative
	 */
	public function setDuration( ?int $seconds ): static
	{
		if( NULL !== $seconds && $seconds < 0 )
			throw new InvalidArgumentException( 'iTunes duration cannot be negative' );
		$this->duration	= $seconds;
		return $this;
	}

	/**
	 *	Sets number of the episode.
	 *	@param		?int		$episode		Number of the episode
	 *	@return		static
	 *	@throws		InvalidArgumentException	if not positive
	 */
	public function setEpisode( ?int $episode ): static
	{
		if( NULL !== $episode && $episode < 1 )
			throw new InvalidArgumentException( 'iTunes episode number must be positive' );
		$this->episode	= $episode;
		return $this;
	}

	/**
	 *	Sets type of the episode: full, trailer or bonus.
	 *	@param		?string		$type		"full", "trailer" or "bonus"
	 *	@return		static
	 *	@throws		InvalidArgumentException	if type is invalid
	 */
	public function setEpisodeType( ?string $type ): static
	{
		if( NULL !== $type && !in_array( $type, self::EPISODE_TYPES, TRUE ) )
			throw new InvalidArgumentException( 'iTunes episode type must be one of: '.implode( ', ', self::EPISODE_TYPES ) );
		$this->episodeType	= $type;
		return $this;
	}

	/**
	 *	Sets flag: episode contains explicit content, NULL if not specified.
	 *	@param		?bool		$explicit		Flag: episode contains explicit content, NULL if not specified
	 *	@return		static
	 */
	public function setExplicit( ?bool $explicit ): static
	{
		$this->explicit	= $explicit;
		return $this;
	}

	/**
	 *	Sets URL of the artwork of the episode.
	 *	@param		?string		$url		URL of the artwork of the episode
	 *	@return		static
	 */
	public function setImage( ?string $url ): static
	{
		$this->image	= $url;
		return $this;
	}

	/**
	 *	Sets number of the season.
	 *	@param		?int		$season		Number of the season
	 *	@return		static
	 *	@throws		InvalidArgumentException	if not positive
	 */
	public function setSeason( ?int $season ): static
	{
		if( NULL !== $season && $season < 1 )
			throw new InvalidArgumentException( 'iTunes season number must be positive' );
		$this->season	= $season;
		return $this;
	}

	/**
	 *	Sets title of the episode, without episode or season number.
	 *	@param		?string		$title		Episode title without episode or season number
	 *	@return		static
	 */
	public function setTitle( ?string $title ): static
	{
		$this->title	= $title;
		return $this;
	}
}
