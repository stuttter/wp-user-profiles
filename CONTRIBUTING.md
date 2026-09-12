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

They use PHP 7.4 and WordPress 5.2.4 to exercise the declared compatibility
floors. Docker is the only host dependency; the wrappers do not read or require
repository or service credentials.

AI-assisted contributions are welcome, but the contributor remains responsible
for understanding and validating the result.
