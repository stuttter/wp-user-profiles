# WP User Profiles contributor guidance

## Compatibility

- Preserve PHP 7.4 and WordPress 6.4 compatibility unless a dedicated pull
  request explicitly changes the published minimums.
- Treat capabilities, nonces, save routing, role changes, multisite context,
  redirects, and user data as critical-risk code.
- Preserve public functions, hooks, filter arguments, section and metabox
  identifiers, form field names, class names, and public properties unless a
  deprecation path is part of the change.
- Always restore switched site context, including error and early-return paths.

## Tests

- Add a regression test before changing observed behavior.
- Characterize self-edit and other-user saves, nonce failures, capability
  checks, redirects, multisite role updates, and third-party hook routing when
  touching those paths.
- Run `composer test`, the centrally managed PHPCS gate, the declared PHP syntax matrix, both
  WordPress integration topologies, and metadata/artifact validation before
  requesting review.

## Release tooling

Keep the plugin header, readme stable tag, changelog, translation catalog, and
release metadata synchronized. Use the repository release commands instead of
editing only one version declaration.

## Automation

Follow the organization-level safety boundaries. AI-authored implementation
must remain a draft pull request and cannot modify workflows, release policy,
ownership, security policy, or this file.
