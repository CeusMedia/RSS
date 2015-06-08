# RSS

Set of PHP classes to generate, read and combine RSS feeds.

## Installation

Use [composer](http://getcomposer.org/) to install.
Or add [packagist](packagist.org) package <code>ceus-media/rss</code> to your composer-driven project.

## Example

```
use \CeusMedia\Rss as Rss;

$channel = new Rss\Model\Channel();
$channel->setTitle("RSS Test Channel");
$channel->setLink("http://example.com/#rss");
$channel->setDescription("...");

$item = new Rss\Model\Item();
$item->setTitle("Item 1");
$item->setLink("http://example.com/#item1");

$channel->addItem($item);
  
$xml = Rss\Renderer::render($channel);
```
