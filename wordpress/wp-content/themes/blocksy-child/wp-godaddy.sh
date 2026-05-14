#!/usr/bin/env bash
#
# Run WP-CLI on GoDaddy shared (cPanel / ea-php-cli) with fewer warnings:
#   - tput: No value for $TERM and no -T specified
#   - Could not ensure ea-php-cli cache directory!
#
# Usage (on the server, after chmod +x):
#   ~/public_html/wp-content/themes/blocksy-child/wp-godaddy.sh option get admin_email
#
# From your Mac (replace host alias and path if different):
#   ssh godaddy 'bash ~/public_html/wp-content/themes/blocksy-child/wp-godaddy.sh option get admin_email'
#
# If the cache warning persists, set a directory you know is writable:
#   export EA_PHP_CLI_CACHE_DIR="$HOME/tmp-ea-php-cli"
#   mkdir -p "$EA_PHP_CLI_CACHE_DIR"
#   bash wp-godaddy.sh plugin list
#
# Last resort on restricted shared hosting: use cPanel “Terminal” or MultiPHP’s
# “php” path from the same user as the site, or ask GoDaddy to repair ea-php-cli
# permissions for your Linux user.

set -e

export TERM="${TERM:-xterm-256color}"

export XDG_CACHE_HOME="${XDG_CACHE_HOME:-$HOME/.cache}"
mkdir -p "$XDG_CACHE_HOME" 2>/dev/null || true

# Many cPanel ea-php-cli builds respect a user-writable cache dir under $HOME:
export EA_PHP_CLI_CACHE_DIR="${EA_PHP_CLI_CACHE_DIR:-$HOME/.ea-php-cli-cache}"
mkdir -p "$EA_PHP_CLI_CACHE_DIR" 2>/dev/null || true
chmod 700 "$EA_PHP_CLI_CACHE_DIR" 2>/dev/null || true

mkdir -p "$HOME/.cpanel/ea-php-cli" 2>/dev/null || true

WP_ROOT="${WP_ROOT:-$HOME/public_html}"
if [[ ! -f "$WP_ROOT/wp-load.php" ]]; then
	echo "wp-godaddy.sh: no wp-load.php in WP_ROOT=$WP_ROOT — export WP_ROOT=/path/to/wordpress" >&2
	exit 1
fi

cd "$WP_ROOT"
exec wp "$@"
