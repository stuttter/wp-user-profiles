#!/usr/bin/env bash

set -Eeuo pipefail

mode="${1:-}"
case "${mode}" in
	single-site)
		payload='single-site-smoke.php'
		;;
	multisite)
		payload='multisite-smoke.php'
		;;
	*)
		echo 'Usage: run-wordpress-smoke.sh single-site|multisite' >&2
		exit 2
		;;
esac

command -v docker >/dev/null 2>&1 || {
	echo 'Docker is required to run the WordPress smoke tests.' >&2
	exit 1
}

integration_directory="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repository_directory="$(cd "${integration_directory}/../.." && pwd)"

test -f "${repository_directory}/wp-user-profiles.php"
test -f "${integration_directory}/${payload}"
test ! -L "${repository_directory}/wp-user-profiles.php"
test ! -L "${integration_directory}/${payload}"

# These are immutable multi-architecture image digests. Keep PHP 7.4 here so
# the live checks exercise the plugin's declared minimum PHP version. The core
# image supplies WordPress 6.4 files only; plugin code runs in the CLI image.
wordpress_cli_image='wordpress:cli-php7.4@sha256:946a8b7f237f6cf90d8f04aff952544a0332d43374d598925dcf0180e4441c6c'
wordpress_core_image='wordpress:6.4-apache@sha256:8ae66efb09a2cc4f1ce44414c52b0ce4198c1f8ed338a6f51910bc09cd7d8bbb'
database_image='mariadb:10.11@sha256:07c0aaff7396b74cb7975cba78257178d188e30f531a5db2b617c48beef13c41'

temporary_parent="${RUNNER_TEMP:-${TMPDIR:-/tmp}}"
temporary_directory="$(mktemp -d "${temporary_parent%/}/wpup-smoke.XXXXXX")"
identifier="$(basename "${temporary_directory}" | tr -cd 'A-Za-z0-9_.-')"
network="${identifier}-network"
database_container="${identifier}-database"
wordpress_data="${identifier}-wordpress"

cleanup() {
	status=$?
	trap - EXIT INT TERM
	docker rm --force --volumes "${database_container}" >/dev/null 2>&1 || true
	docker network rm "${network}" >/dev/null 2>&1 || true
	docker volume rm --force "${wordpress_data}" >/dev/null 2>&1 || true
	if [[ -n "${temporary_directory}" && "${temporary_directory}" == "${temporary_parent%/}/wpup-smoke."* ]]; then
		rm -rf -- "${temporary_directory}"
	fi
	exit "${status}"
}
trap cleanup EXIT INT TERM

docker network create "${network}" >/dev/null
docker volume create "${wordpress_data}" >/dev/null
docker run --rm \
	--entrypoint sh \
	--volume "${wordpress_data}:/target" \
	"${wordpress_core_image}" \
	-c 'cp -a /usr/src/wordpress/. /target/'
docker run --rm \
	--user root \
	--entrypoint chown \
	--volume "${wordpress_data}:/var/www/html" \
	"${wordpress_cli_image}" \
	-R www-data:www-data /var/www/html
docker run --detach \
	--name "${database_container}" \
	--network "${network}" \
	--env MARIADB_DATABASE=wordpress \
	--env MARIADB_USER=wordpress \
	--env MARIADB_PASSWORD=wordpress-smoke \
	--env MARIADB_ROOT_PASSWORD=wordpress-smoke-root \
	"${database_image}" >/dev/null

database_ready='false'
for _attempt in {1..60}; do
	if docker exec "${database_container}" healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1; then
		database_ready='true'
		break
	fi
	sleep 1
done
if [[ "${database_ready}" != 'true' ]]; then
	echo 'MariaDB did not become ready within 60 seconds.' >&2
	docker logs "${database_container}" >&2 || true
	exit 1
fi

wp_core() {
	docker run --rm \
		--network "${network}" \
		--env HOME=/tmp \
		--volume "${wordpress_data}:/var/www/html" \
		"${wordpress_cli_image}" \
		wp --allow-root --path=/var/www/html "$@"
}

wp_plugin() {
	docker run --rm \
		--network "${network}" \
		--env HOME=/tmp \
		--volume "${wordpress_data}:/var/www/html" \
		--volume "${repository_directory}:/var/www/html/wp-content/plugins/wp-user-profiles:ro" \
		--volume "${integration_directory}:/wpup-integration:ro" \
		"${wordpress_cli_image}" \
		wp --allow-root --path=/var/www/html "$@"
}

wp_core config create \
	--dbname=wordpress \
	--dbuser=wordpress \
	--dbpass=wordpress-smoke \
	--dbhost="${database_container}:3306" \
	--skip-check

if [[ "${mode}" == 'multisite' ]]; then
	wp_core core multisite-install \
		--url=wpup.example.test \
		--title='WP User Profiles smoke test' \
		--admin_user=administrator \
		--admin_password=wordpress-smoke-admin \
		--admin_email=administrator@example.test \
		--skip-email \
		--subdomains
else
	wp_core core install \
		--url=wpup.example.test \
		--title='WP User Profiles smoke test' \
		--admin_user=administrator \
		--admin_password=wordpress-smoke-admin \
		--admin_email=administrator@example.test \
		--skip-email
fi

wp_plugin plugin activate wp-user-profiles
wp_plugin eval-file "/wpup-integration/${payload}"
