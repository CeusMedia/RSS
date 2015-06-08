# RSS

Set of PHP classes to generate, read and combine RSS feeds.

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
