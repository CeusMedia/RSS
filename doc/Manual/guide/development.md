# Development

## Tests and static analysis

```sh
composer dev:test-all            # syntax, coding style, PHPStan, unit tests
composer dev:test-units          # unit tests
composer dev:test-coverage       # unit tests with code coverage, report in doc/Test/Coverage/
composer dev:infection           # mutation tests, report in doc/Test/Infection/
composer dev:stan                # static analysis (level max + strict rules)
composer dev:cs                  # coding style check, dev:cs-fix to fix
composer dev:rector              # Rector suggestions for PHP 8.3
composer dev:test-syntax         # PHP syntax check
composer dev:demo                # web server for demos on http://localhost:8080/
```

Coverage and mutation tests need Xdebug or PCOV. Tests of `UrlReader` start local PHP web servers,
no internet connection is needed. GitHub Actions runs all checks on PHP 8.3, 8.4 and 8.5.

## Documentation

This documentation is built with [MkDocs](https://www.mkdocs.org/) and
[Material for MkDocs](https://squidfunk.github.io/mkdocs-material/).

```sh
composer dev:docs                # generate API reference and build site, open doc/Site/index.html
composer dev:docs-serve          # live preview on http://localhost:8000
composer dev:docs-api            # only generate API reference (doc/Manual/api/)
composer dev:docs-install        # install MkDocs into a Python venv (optional, else Docker is used)
composer dev:create-docs         # classic API documentation by DocCreator into doc/API/
```

| Path | Content |
|------|---------|
| `tool/mkdocs.yml` | configuration and navigation |
| `doc/Site/` | built HTML site, **generated** |
| `doc/Manual/` | hand-written pages (guides) and assets |
| `doc/Manual/api/` | API reference, **generated** – do not edit |
| `tool/docs/generate-api.php` | generator: reads classes by reflection and their doc blocks |
| `tool/docs/examples/` | examples, included into the API page of a class (same path as class) |
| `tool/docs/docs.sh` | runs MkDocs: local installation, Python venv or Docker |

MkDocs is taken from the first available source:

1. a Python virtual environment in `tool/docs/.venv` (created by `composer dev:docs-install`),
2. `mkdocs` in `PATH` (with `mkdocs-material` installed),
3. the Docker image `squidfunk/mkdocs-material`.

!!! tip "Documenting a class"
    Summary and description of classes and methods come from doc blocks, types from the signatures.
    Properties promoted in the constructor are described by the `@param` of the constructor.
    To add examples to a class page, create a Markdown file in `tool/docs/examples/`,
    e.g. `tool/docs/examples/Combiner.md`.
