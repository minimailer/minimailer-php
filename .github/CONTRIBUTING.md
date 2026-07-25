# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand this guide before creating an issue or pull request.

## Etiquette

Maintainers give their free time to build and maintain this code and make it freely
available in the hope it will be of use. Please be considerate when raising issues or
presenting pull requests. It is the maintainer's duty to ensure submissions are of
sufficient quality to benefit the project; respect that decision.

## Before filing an issue

- Attempt to reproduce the problem, to confirm it wasn't a coincidental incident.
- Check that your feature suggestion isn't already present.
- Check the pull requests tab for a fix or feature already in progress.

Security issues do **not** belong in the issue tracker — see [SECURITY.md](SECURITY.md).

## Before submitting a pull request

Run both gates locally; CI runs exactly these and will fail the run on either:

```bash
composer lint    # pint --test — checks style, does not fix it
composer test    # pest
```

If `composer lint` fails, fix it with `vendor/bin/pint` and commit the result. CI never
formats your branch for you.

This package supports **PHP 8.2+**, so avoid syntax newer than 8.2 — the test matrix
covers PHP 8.2 to 8.5 on Linux, Windows and macOS, against both `prefer-lowest` and
`prefer-stable` dependency resolutions. If you change a dependency constraint, run:

```bash
composer update --prefer-lowest --prefer-dist
composer test
```

The lowest-bound resolution is what catches a constraint widened past what the code
actually supports.

## Requirements

- **Document any change in behaviour** — keep `README.md` and any other relevant docs current.
- **Consider the release cycle** — this project follows [SemVer v2.0.0](https://semver.org/).
  The SDK is pinned to the MiniMailer v1 API contract; changes to the public surface
  (`MiniMailer`, `Client`, `Response`, the resource classes and the exception hierarchy)
  are breaking changes and are treated as such.
- **One pull request per feature** — send multiple PRs if you want to do more than one thing.
- **Send coherent history** — make each commit meaningful; squash intermediate work before submitting.
- **Add a test** — new behaviour needs a Pest test. Tests are unit-only and must not make
  real network calls; use the `fakeMiniMailer()` helper or a Guzzle `MockHandler` stack.

**Happy coding**!
