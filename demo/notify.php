<?php
declare( strict_types=1 );

/**
 *	Demo: Notifications for new feed entries, e.g. for Slack, Matrix or Teams.
 *
 *	Remembers the GUIDs (or links) of seen entries in a state file and reports only new ones.
 *	Run it twice: the second run reports nothing new.
 *
 *	In production you would
 *	- read feeds with UrlReader::fetch(), passing ETag and Last-Modified of the previous run,
 *	  so unchanged feeds are not downloaded again (response status 304),
 *	- send the payloads to a webhook instead of printing them.
 *
 *	Usage: php demo/notify.php [--reset]
 */

use CeusMedia\RSS\FeedParser;
use CeusMedia\RSS\Model\Item;

require_once __DIR__.'/bootstrap.php';

$stateFile	= sys_get_temp_dir().'/ceusmedia-rss-demo-notify.json';
if( in_array( '--reset', $argv ?? [], TRUE ) && file_exists( $stateFile ) )
	unlink( $stateFile );

/** @var array<string, bool> $seen */
$seen	= file_exists( $stateFile ) ? (array) json_decode( (string) file_get_contents( $stateFile ), TRUE ) : [];

$identify	= static fn( Item $item ): ?string => $item->getGuid()->value ?? $item->getLink();

$new	= 0;
foreach( ['news.rss', 'releases.atom'] as $file ){
	$channel	= FeedParser::parse( (string) file_get_contents( __DIR__.'/data/'.$file ) );
	foreach( $channel->getItems() as $item ){
		$id	= $identify( $item );
		if( NULL === $id || isset( $seen[$id] ) )
			continue;
		$seen[$id]	= TRUE;
		$new++;
		//  payload in the format of Slack incoming webhooks
		$payload	= [
			'text'	=> sprintf( '*%s*: <%s|%s>', $channel->getTitle(), $item->getLink() ?? $channel->getLink(), $item->getTitle() ?? 'New entry' ),
		];
		print json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ).PHP_EOL;
	}
}

file_put_contents( $stateFile, json_encode( $seen ) );
print PHP_EOL.( $new > 0 ? $new.' new entries.' : 'Nothing new.' ).' State: '.$stateFile.PHP_EOL;
