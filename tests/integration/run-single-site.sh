#!/usr/bin/env bash

set -Eeuo pipefail

integration_directory="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
exec bash "${integration_directory}/run-wordpress-smoke.sh" single-site
