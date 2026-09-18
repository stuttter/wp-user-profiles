# Contributing

Thanks for helping maintain WP User Profiles.

## Before changing behavior

Describe the observable behavior, compatibility expectations, and acceptance
criteria in a GitHub issue. Report suspected vulnerabilities privately through
[GitHub Security Advisories](https://github.com/stuttter/wp-user-profiles/security/advisories/new).

## Pull requests

* Keep each pull request focused and reversible.
* Add regression coverage for behavior changes and bug fixes when a suitable
  test harness exists.
* Preserve the declared PHP and WordPress minimum versions.
* Test both self-editing and editing another user when the affected code
  supports both.
* Test single-site, multisite Network Admin, and User Admin behavior where
  applicable.
* Identify capability, nonce, role, status, privacy, dependency, and
  release-process implications explicitly.
* Rebuild and review generated assets when their sources change.
* Do not commit credentials, build caches, development databases, or generated
  release ZIP files.
* Wait for every required check and resolve review conversations before merge.

## Live WordPress smoke tests

The integration wrappers provision isolated WordPress and MariaDB containers,
activate the plugin, run the existing PHP smoke payload, and always remove the
containers, network, and temporary WordPress files when they finish:

```sh
tests/integration/run-single-site.sh
tests/integration/run-multisite.sh
```

They use PHP 7.4 and WordPress 6.4 to exercise the declared compatibility
floors. Docker is the only host dependency; the wrappers do not read or require
repository or service credentials.

AI-assisted contributions are welcome, but the contributor remains responsible
for understanding and validating the result.

## Release and translation chores

`readme.txt` is the canonical WordPress.org listing. `README.md` is a concise,
human-maintained GitHub overview; it is not generated from `readme.txt`.

Install the locked tools with `composer install`, then use:

* `composer release:version -- 2.7.0` to update the plugin header, WordPress.org
  stable tag, `package.json`, and both package-lock declarations.
* `composer release:check` to fail when any of those version declarations drift.
* the centrally managed PHPCS gate to validate WordPress coding standards,
  translatable PHP strings, and their text domain.
* `composer i18n` to regenerate the tracked POT file with the locked WP-CLI.
* `composer i18n:check` to regenerate to a temporary file and fail on drift.

Run `composer test` and `npm run build:check` before opening a release pull
request, and require the centrally managed PHPCS gate to pass before merge.
Review every generated diff. Commands return nonzero on a failed check; do not
suppress their exit status.

Production ZIP creation belongs exclusively to the central Stuttter artifact
builder already invoked by CI. Version/tag validation, GitHub releases, and
WordPress.org Subversion publication belong to the central release workflow
once this repository is separately onboarded to that protected lane. Do not add
or commit a second local release archive.
