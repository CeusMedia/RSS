# Extensions

Elements of other namespaces – like `itunes:duration` or `content:encoded` – are handled by extensions.
Parsers and renderer use all built-in extensions by default.

## Built-in extensions

| Extension | Prefix | Data | Elements |
|-----------|--------|------|----------|
| [Atom](../api/Extension/Atom/AtomExtension.md) | `atom` | [`Atom\ChannelData`](../api/Extension/Atom/ChannelData.md) | `atom:link`, e.g. `rel="self"` or WebSub `rel="hub"` |
| [Content](../api/Extension/Content/ContentExtension.md) | `content` | [`Content\ItemData`](../api/Extension/Content/ItemData.md) | `content:encoded`, the full content of an item |
| [Dublin Core](../api/Extension/DublinCore/DublinCoreExtension.md) | `dc` | [`DublinCore\Data`](../api/Extension/DublinCore/Data.md) | `dc:creator`, authors without email address |
| [iTunes](../api/Extension/ITunes/ITunesExtension.md) | `itunes` | [`ITunes\ChannelData`](../api/Extension/ITunes/ChannelData.md), [`ITunes\ItemData`](../api/Extension/ITunes/ItemData.md) | podcast elements, see [Podcasts](podcasts.md) |

## Extension data

Channels and items store data of extensions, keyed by class:

```php
use CeusMedia\RSS\Extension\Content\ItemData as ContentData;
use CeusMedia\RSS\Extension\DublinCore\Data as DublinCoreData;

$item->setExtension( new ContentData( '<p>Full article …</p>' ) );
$item->setExtension( new DublinCoreData( ['Anna Author', 'Gustav Guest'] ) );

$content	= $item->getExtension( ContentData::class )?->encoded;     // typed, NULL if not set
$item->hasExtension( ContentData::class );
$item->removeExtension( ContentData::class );
```

The renderer declares the namespace of an extension on the root element – only if the channel or one of its
items has data of the extension.

## Choosing extensions

```php
use CeusMedia\RSS\Extension\Extensions;

$channel	= FeedParser::parse( $xml, Extensions::none() );           // ignore all extension elements
$xml		= Renderer::render( $channel, FALSE, Extensions::none() ); // do not render extension data
```

## Own extensions

Extend [`Extension\AbstractExtension`](../api/Extension/AbstractExtension.md) and implement the methods needed:

```php
use CeusMedia\RSS\Dom;
use CeusMedia\RSS\Extension\AbstractExtension;
use CeusMedia\RSS\Model\Channel;

final class PodcastLocked
{
	public function __construct( public readonly bool $locked ) {}
}

class PodcastIndexExtension extends AbstractExtension
{
	public function getNamespace(): string
	{
		return 'https://podcastindex.org/namespace/1.0';
	}

	public function getPrefix(): string
	{
		return 'podcast';
	}

	public function parseChannelElement( DOMElement $element, Channel $channel ): void
	{
		if( 'locked' === $element->localName )
			$channel->setExtension( new PodcastLocked( 'yes' === Dom::getText( $element ) ) );
	}

	public function renderChannel( Channel $channel, DOMElement $node ): void
	{
		$data	= $channel->getExtension( PodcastLocked::class );
		if( NULL !== $data )
			$this->append( $node, 'locked', $data->locked ? 'yes' : 'no' );
	}

	protected function getDataClasses(): array
	{
		return [PodcastLocked::class];
	}
}

$extensions	= Extensions::default()->add( new PodcastIndexExtension() );
$channel	= FeedParser::parse( $xml, $extensions );
$xml		= Renderer::render( $channel, FALSE, $extensions );
```

`append()` creates elements in the namespace of the extension. Values are escaped and sanitized like all other values.
