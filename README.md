# RSS

![Branch](https://img.shields.io/badge/Branch-0.3.x-blue?style=flat-square)
![Release](https://img.shields.io/badge/Release-0.3.0-blue?style=flat-square)
![PHP version](https://img.shields.io/badge/PHP-%5E7.4|%5E8.1-blue?style=flat-square&color=777BB4)
![PHPStan level](https://img.shields.io/badge/PHPStan_----darkgreen?style=flat-square)

Set of PHP classes to generate, read and combine RSS feeds.

## Installation

Use [composer](http://getcomposer.org/) to install.
Or add [packagist](packagist.org) package <code>ceus-media/rss</code> to your composer-driven project.

## Example

```
use \CeusMedia\RSS as RSS;

$channel = new RSS\Model\Channel();
$channel->setTitle("RSS Test Channel");
$channel->setLink("http://example.com/#rss");
$channel->setDescription("...");

$item = new RSS\Model\Item();
$item->setTitle("Item 1");
$item->setLink("http://example.com/#item1");

$channel->addItem($item);

$xml = RSS\Renderer::render($channel);
```
