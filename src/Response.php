<?php
declare( strict_types=1 );

/**
 *	Response of reading a feed from a URL.
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
namespace CeusMedia\RSS;

/**
 *	Response of reading a feed from a URL.
 *
 *	@category		Library
 *	@package		CeusMedia_RSS
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2026 {@link https://ceusmedia.de/ Ceus Media}
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/RSS
 */
final readonly class Response
{
	/**
	 *	Creates response.
	 *	@param		string		$url			Requested URL
	 *	@param		int			$status			HTTP status code of last response (after redirects)
	 *	@param		string		$content		Body, empty if not modified
	 *	@param		?string		$etag			Value of ETag header, to be used for next conditional request
	 *	@param		?string		$lastModified	Value of Last-Modified header, to be used for next conditional request
	 *	@param		?string		$contentType	Value of Content-Type header
	 *	@param		string		$effectiveUrl	URL after redirects
	 */
	public function __construct(
		public string $url,
		public int $status,
		public string $content,
		public ?string $etag = NULL,
		public ?string $lastModified = NULL,
		public ?string $contentType = NULL,
		public string $effectiveUrl = '',
	)
	{
	}

	/**
	 *	Indicates that the feed has not changed since the last request (HTTP 304).
	 *	@return		bool
	 */
	public function isNotModified(): bool
	{
		return 304 === $this->status;
	}
}
