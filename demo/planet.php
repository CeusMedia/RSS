<?php
declare( strict_types=1 );

/**
 *	Demo: "Planet" – a static HTML page and a feed combining several feeds (RSS and Atom).
 *
 *	Typical use: a cron job reads the feeds of a team, project or community
 *	and writes a static page and a combined feed.
 *
 *	- Duplicates are removed (same GUID or link).
 *	- Each entry links to its original feed (<source>).
 *	- Content of feeds is not trusted: HTML is stripped, all output is escaped.
 *
 *	Usage: php demo/planet.php [--feed]
 *	       --feed    output combined RSS feed instead of HTML page
 */

use CeusMedia\RSS\Combiner;
use CeusMedia\RSS\Extension\Atom\ChannelData as AtomData;
use CeusMedia\RSS\Model\Channel;

require_once __DIR__.'/bootstrap.php';

//  in production, use $combiner->addUrls( [...] ) to read feeds in parallel and tolerate failing feeds
$feeds	= [
	'https://news.example.org/feed.rss'		=> __DIR__.'/data/news.rss',
	'https://podcast.example.org/feed.rss'	=> __DIR__.'/data/podcast.rss',
	'https://code.example.org/releases.atom'	=> __DIR__.'/data/releases.atom',
];
$combiner	= new Combiner();
foreach( $feeds as $url => $file )
	$combiner->add( (string) file_get_contents( $file ), $url );

$planet	= ( new Channel( 'Planet Example', 'https://planet.example.org/', 'News, releases and podcast episodes of example.org' ) )
	->setExtension( AtomData::self( 'https://planet.example.org/feed.rss' ) );

if( in_array( '--feed', $argv ?? [], TRUE ) || 'feed' === ( $_GET['format'] ?? NULL ) ){
	if( 'cli' !== PHP_SAPI )
		header( 'Content-Type: application/rss+xml; charset=utf-8', TRUE );
	print $combiner->render( $planet, 20 );
	exit;
}

$escape	= static fn( ?string $text ): string => htmlspecialchars( $text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
//  tags are replaced by spaces, so list items or paragraphs do not stick together
$teaser	= static fn( ?string $html ): string => mb_strimwidth( trim( (string) preg_replace( '/\s+/', ' ', strip_tags( str_replace( '<', ' <', $html ?? '' ) ) ) ), 0, 160, '…' );

$entries	= [];
foreach( $combiner->combine( 20 ) as $item ){
	$entries[]	= sprintf(
		'<article><h2><a href="%s">%s</a></h2><p class="meta">%s · from <a href="%s">%s</a></p><p>%s</p></article>',
		$escape( $item->getLink() ?? '#' ),
		$escape( $item->getTitle() ?? $teaser( $item->getDescription() ) ),
		$escape( $item->getPubDate()?->format( 'Y-m-d H:i' ) ?? 'undated' ),
		$escape( $item->getSource()?->url ),
		$escape( $item->getSource()?->title ),
		$escape( $teaser( $item->getDescription() ) )
	);
}

if( 'cli' !== PHP_SAPI )
	header( 'Content-Type: text/html; charset=utf-8', TRUE );
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= $escape( $planet->getTitle() ) ?></title>
	<link rel="alternate" type="application/rss+xml" title="<?= $escape( $planet->getTitle() ) ?>" href="?format=feed">
	<style>
		body{font-family:sans-serif;max-width:42em;margin:2em auto;line-height:1.4}
		article{border-bottom:1px solid #ddd;padding:.5em 0} h2{font-size:1.1em;margin:.2em 0} .meta{color:#666;font-size:.85em;margin:0}
	</style>
</head>
<body>
	<h1><?= $escape( $planet->getTitle() ) ?></h1>
	<p><?= $escape( $planet->getDescription() ) ?> · <a href="?format=feed">RSS</a></p>
	<?= implode( PHP_EOL."\t", $entries ).PHP_EOL ?>
</body>
</html>
