# RSS

![Branch](https://img.shields.io/badge/Branch-0.4.x-blue?style=flat-square)
![Release](https://img.shields.io/badge/Release-0.4.0-blue?style=flat-square)
![PHP version](https://img.shields.io/badge/PHP-%5E8.1-blue?style=flat-square&color=777BB4)
![PHPStan level](https://img.shields.io/badge/PHPStan_-5-darkgreen?style=flat-square)

Set of PHP classes to generate, read and combine RSS feeds.

## Installation

Use [composer](http://getcomposer.org/) to install.
Or add [packagist](https://packagist.org/) package <code>ceus-media/rss</code> to your composer-driven project.

## Example

```
use \CeusMedia\RSS as RSS;

$channel = new RSS\Model\Channel();
$channel->setTitle('RSS Test Channel');
$channel->setLink('https://example.com/#rss');
$channel->setDescription('...');

$item = new RSS\Model\Item();
$item->setTitle('Item 1');
$item->setLink('https://example.com/#item1');

$channel->addItem($item);

$xml = RSS\Renderer::render($channel);
```
