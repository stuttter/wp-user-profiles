#!/usr/bin/env bash

set -euo pipefail

repository_path="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
pot_path="${repository_path}/wp-user-profiles/assets/languages/wp-user-profiles.pot"
mode="${1:-generate}"

if [[ "${mode}" != "generate" && "${mode}" != "--check" ]]; then
	echo "Use composer i18n or composer i18n:check." >&2
	exit 2
fi

wp_cli_bootstrap="${repository_path}/vendor/wp-cli/wp-cli/php/boot-fs.php"

if [[ ! -f "${wp_cli_bootstrap}" ]]; then
	echo "Missing locked WP-CLI. Run composer install first." >&2
	exit 1
fi

temporary_directory="$(mktemp -d)"
temporary_pot="${temporary_directory}/wp-user-profiles.pot"
trap 'rm -f "${temporary_pot}"; rmdir "${temporary_directory}"' EXIT

php -d 'error_reporting=E_ALL & ~E_DEPRECATED' "${wp_cli_bootstrap}" i18n audit \
	"${repository_path}" \
	--slug=wp-user-profiles \
	--domain=wp-user-profiles \
	--exclude=.git,bin,build,node_modules,tests,vendor \
	--format=github-actions

php -d 'error_reporting=E_ALL & ~E_DEPRECATED' "${wp_cli_bootstrap}" i18n make-pot \
	"${repository_path}" \
	"${temporary_pot}" \
	--slug=wp-user-profiles \
	--domain=wp-user-profiles \
	--exclude=.git,bin,build,node_modules,tests,vendor \
	--headers='{"Report-Msgid-Bugs-To":"https://github.com/stuttter/wp-user-profiles/issues","POT-Creation-Date":""}' \
	--file-comment=$'Copyright (C) Triple J Software, Inc.\nThis file is distributed under the GPLv2 or later.' \
	--package-name='WP User Profiles'

if [[ "${mode}" == "--check" ]]; then
	if ! cmp --silent "${temporary_pot}" "${pot_path}"; then
		echo "Translation template is stale. Run composer i18n." >&2
		if diff --unified "${pot_path}" "${temporary_pot}"; then
			echo "Translation files unexpectedly compared equal." >&2
			exit 2
		else
			diff_status=$?
			if [[ "${diff_status}" -gt 1 ]]; then
				exit "${diff_status}"
			fi
		fi
		exit 1
	fi

	echo "Translation template is current."
	exit 0
fi

mv "${temporary_pot}" "${pot_path}"
echo "Updated ${pot_path}."
