<?php
declare( strict_types=1 );

/**
 *	Demo overview for the browser.
 *	Start with: php -S localhost:8080 -t demo
 */

$demos	= [
	'create.php'	=> 'Create an RSS feed from scratch',
	'read.php'		=> 'Read and display an RSS or Atom feed',
	'combine.php'	=> 'Combine a news feed and a podcast into one feed',
	'errors.php'	=> 'Error handling of parser and renderer',
	'podcast.php'	=> 'Podcast feed for Apple Podcasts and Spotify (iTunes extension)',
	'planet.php'	=> 'Planet: static HTML page and feed combining RSS and Atom feeds',
	'notify.php'	=> 'Notifications for new entries (Slack webhook payloads)',
	'filter.php'	=> 'Filtered feed: only releases of the last weeks',
];
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>CeusMedia/RSS – Demos</title>
	<style>body{font-family:sans-serif;max-width:40em;margin:2em auto}</style>
</head>
<body>
	<h1>CeusMedia/RSS – Demos</h1>
	<p>All demos can also be run on the command line, e.g. <code>php demo/create.php</code>.</p>
	<ul>
<?php foreach( $demos as $file => $label ): ?>
		<li><a href="<?= htmlspecialchars( $file ) ?>"><?= htmlspecialchars( $label ) ?></a></li>
<?php endforeach ?>
	</ul>
</body>
</html>
