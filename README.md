# RSS

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
