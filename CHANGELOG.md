# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased] – 0.5.0

Requires PHP 8.3. **This release changes the API**, see [Upgrading from 0.4 to 0.5](#upgrading-from-04-to-05).

### Added
- Support of all RSS 2.0 elements: `enclosure`, `comments`, multiple `category` elements with `domain`,
  `ttl`, `rating`, `textInput`, `skipHours`, `skipDays` and `lastBuildDate` of channel.
- Reading of Atom 1.0 feeds with `AtomParser`, mapped to the same models.
- `FeedParser` detecting the format (RSS 2.0 or Atom 1.0) automatically.
- Extensions for namespaced elements, registered in `Extension\Extensions`:
  - Atom: `atom:link`, e.g. `rel="self"` and WebSub `rel="hub"`
  - Content: `content:encoded`
  - Dublin Core: `dc:creator`
  - iTunes: `itunes:*` for podcasts (Apple Podcasts, Spotify)
  - Own extensions by extending `Extension\AbstractExtension`.
- Extension data on channels and items: `setExtension()`, `getExtension()`, `hasExtension()`, `removeExtension()`.
- Value objects `Person`, `Guid`, `Category`, `Source`, `Enclosure`, `Cloud` and `TextInput`.
- `UrlReader` to read feeds from URLs with secure settings: TLS verification, HTTP(S) only, timeout,
  size limit, at most 5 redirects, compression, own user agent.
- Conditional requests with ETag and Last-Modified: `UrlReader::fetch()` returns a `Response`.
- Parallel reading of several feeds: `UrlReader::fetchAll()` and `Combiner::addUrls()`, failing feeds are reported.
- Combiner removes duplicates (same GUID or link), adds `<source>` to items and supports filters (`Filter`).
- Exceptions `ReadException`, `ParseException` and `RenderException`, all extending `RuntimeException`.
- Validation of values in models and value objects (image size, TTL, skip hours and days, cloud port, …),
  throwing `InvalidArgumentException`.
- Constructors with the required values: `new Channel($title, $link, $description)`,
  `new Item($title, $link, $description)`, `new Image($url, $title, $link)`.
- Demos in `demo/`, runnable on command line and in browser: create, read, combine, errors,
  podcast, planet, notifications and filters.
- Unit tests for all classes with a line coverage of more than 99 % and a mutation score of 99 %.

### Changed
- Dates are `DateTimeImmutable` instead of UNIX timestamps and keep their timezone when rendered.
- Method names follow the RSS specification, for example `setPubDate()`, `setGuid()`, `setManagingEditor()`.
- Renderer builds XML using DOM: all values and attributes are escaped, characters not allowed in XML are removed,
  invalid UTF-8 sequences are replaced by U+FFFD.
- Parser uses DOM, loaded by `XML\DOM\SyntaxValidator` of CeusMedia/Common. Error messages contain all XML errors.
- Parser passes elements of other namespaces to extensions and ignores unknown ones,
  so extension elements like `media:title` cannot overwrite RSS elements anymore.
- Parser is lenient: invalid optional values (dates, sizes, TTL, …) are skipped instead of failing.
- Parser trims all values.
- Output declares `encoding="UTF-8"`, root element has `version="2.0"` (was `2.0.11`).
- `isPermaLink` of GUIDs is always rendered explicitly.
- Empty optional elements are not rendered anymore.
- `Combiner::render()` does not modify the given channel anymore.
- `Combiner::combine()` compares dates across timezones.
- `Combiner::add()` accepts RSS and Atom, and an optional URL of the feed.
- Reading from URLs uses cURL directly instead of `Net\Reader` of CeusMedia/Common,
  to support parallel and conditional requests.

### Fixed
- Attribute values were not escaped, so an `&` in a URL produced an empty document.
- XML errors while rendering produced an empty document and PHP warnings instead of an exception.
- Missing channel title, link or description was not detected if `NULL`.
- `isPermaLink` was interpreted inverted: a missing attribute means `true` by specification.
- Items with a description but without a title could not be rendered.
- Images were rendered with empty `link`, `width`, `height` and `description` elements.
- `docs` of channel was ignored on rendering.
- Names of persons containing parentheses were broken on parsing.
- Feeds without `<channel>` and non-RSS feeds (like Atom) produced PHP warnings and an empty channel.
- `Combiner::addUrl()` did not verify TLS certificates and had no timeout.

### Removed
- `Renderer::$version`, use the constant `Renderer::VERSION`.
- Tuple arrays for persons, GUIDs and sources, replaced by value objects.
- Legacy PHPUnit configurations `tool/phpunit8.xml` and `tool/phpunit9.xml`.
- `Makefile`, replaced by Composer scripts (`composer dev:demo` etc.).

### Development
- PHPUnit 10 with strict coverage metadata, PHPStan 2 on level max with strict rules, Rector with PHP 8.3 rules.
- Coding style checked by PHP-CS-Fixer, mutation tests with Infection (minimum MSI 95 %).
- GitHub Actions workflow running all checks and demos on PHP 8.3, 8.4 and 8.5, and building the documentation.
- Composer scripts `dev:test-all`, `dev:test-coverage`, `dev:infection`, `dev:cs`, `dev:cs-fix`, `dev:demo`, `dev:create-docs`.
- Documentation with MkDocs (Material): guide and API reference generated from doc blocks, built by `composer dev:docs`.
- Complete doc blocks for all classes, methods, constants and properties.

## [0.4.0] – 2023-11-11

### Changed
- Requires PHP 8.1, support of PHP 7.4 dropped.
- Requires CeusMedia/Common 1.0.

## [0.3.0] – 2023-11-11

### Added
- Support of PHP 7.4 and PHP 8.1.
- PHPStan (level 5), Rector and Composer scripts for development.

### Changed
- Improved code style and unit tests.

## [0.2] – 2016-10-11

### Changed
- Package renamed to `ceus-media/rss`.

### Added
- PHPUnit, DocCreator and Makefile configuration.

## [0.1] – 2015-06-08

### Added
- First release as Composer package, migrated from CeusMedia modules.
- Classes in namespace `CeusMedia\RSS`: `Parser`, `Renderer`, `Combiner` and models `Channel`, `Item`, `Image`.
- Demo.

## Upgrading from 0.4 to 0.5

### Channel

| 0.4                                                   | 0.5 |
|-------------------------------------------------------|-----|
| `new Channel()` and `setTitle()` …                    | `new Channel($title, $link, $description)`, setters still exist |
| `getTitle(): ?string`, also link and description      | `getTitle(): string`, empty string if not set |
| `setManager($email, $name)`, `getManager(): array`    | `setManagingEditor(new Person($email, $name))`, `getManagingEditor(): ?Person` |
| `setAdmin($email, $name)`, `getAdmin(): array`        | `setWebMaster(new Person($email, $name))`, `getWebMaster(): ?Person` |
| `setDate(int)`, `getDate(): ?int`                     | `setPubDate(DateTimeInterface)`, `getPubDate(): ?DateTimeImmutable` |
| `setLastBuildDate(int)`                               | `setLastBuildDate(DateTimeInterface)` |
| `setCategory(string)`, `getCategory()`                | `addCategory(string\|Category)`, `getCategories(): list<Category>` |
| `setCloud(array)`, `getCloud(): array`                | `setCloud(new Cloud(…))`, `getCloud(): ?Cloud` |
| `getDocs(): string`                                   | `getDocs(): ?string`, `setDocs(NULL)` omits the element |

### Item

| 0.4                                                   | 0.5 |
|-------------------------------------------------------|-----|
| `setContent()`, `getContent()`                        | `setDescription()`, `getDescription()` |
| `setAuthor($email, $name)`, `getAuthor(): array`      | `setAuthor(new Person($email, $name))`, `getAuthor(): ?Person` |
| `setId($uri, $isPermaLink = FALSE)`, `getId(): array` | `setGuid(new Guid($uri, $isPermaLink = TRUE))`, `getGuid(): ?Guid` |
| `setDate(int)`, `getDate(): ?int`                     | `setPubDate(DateTimeInterface)`, `getPubDate(): ?DateTimeImmutable` |
| `setCategory(string)`, `getCategory()`                | `addCategory(string\|Category)`, `getCategories(): list<Category>` |
| `setSource($label, $url)`, `getSource(): array`       | `setSource(new Source($title, $url))`, `getSource(): ?Source`, URL is required |

> **Note:** A GUID is a permalink by default now, as defined by the RSS specification.
> Pass `isPermaLink: false` for identifiers which are not URLs.

### Image

| 0.4                                                   | 0.5 |
|-------------------------------------------------------|-----|
| `new Image()` and `setUrl()`                          | `new Image($url, $title = NULL, $link = NULL)` |
| `setWidth()`, `setHeight()` accept any integer        | width 1–144, height 1–400, otherwise `InvalidArgumentException` |

### Parser, Renderer and Combiner

- `Renderer::$version` was removed, use `Renderer::VERSION`.
- `Renderer::render()` throws `RenderException`, `Parser::parse()` throws `ParseException`.
  Both extend `RuntimeException`, so existing `catch` blocks still work.
- `Combiner::addUrl()` throws `ReadException` instead of exceptions of CeusMedia/Common.
- `Combiner::add()` parses Atom feeds too; it throws `ParseException` only for unsupported formats.
- A parsed channel returns `NULL` for `getDocs()` if the feed has no `docs` element.
- Setters return `static` instead of `self`.

[Unreleased]: https://github.com/CeusMedia/RSS/compare/0.3.0...HEAD
[0.4.0]: https://github.com/CeusMedia/RSS/commit/2cf2d7c
[0.3.0]: https://github.com/CeusMedia/RSS/compare/0.2...0.3.0
[0.2]: https://github.com/CeusMedia/RSS/compare/0.1...0.2
[0.1]: https://github.com/CeusMedia/RSS/releases/tag/0.1
