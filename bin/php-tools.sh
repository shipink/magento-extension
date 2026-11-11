#!/usr/bin/env bash
# Runs this module's PHP dev checks on the Magento test server, so no local PHP
# or Composer is needed. The server keeps tests/vendor between runs.
#
# Usage:
#   bin/php-tools.sh phpunit [phpunit args...]   e.g. bin/php-tools.sh phpunit --filter UrlValidatorTest
#   bin/php-tools.sh phpcs   [phpcs args...]
#
# Needs SHIPINK_TEST_HOST (user@host) and SHIPINK_TEST_KEY (ssh key path) in the environment.
set -euo pipefail
if [ -z "${SHIPINK_TEST_HOST:-}" ] || [ -z "${SHIPINK_TEST_KEY:-}" ]; then
  echo 'usage: SHIPINK_TEST_HOST=user@host SHIPINK_TEST_KEY=/path/to/ssh.key bin/php-tools.sh phpunit|phpcs [args...]' >&2
  exit 2
fi
KEY=$SHIPINK_TEST_KEY
HOST=$SHIPINK_TEST_HOST
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
tool=${1:?usage: bin/php-tools.sh phpunit|phpcs [args...]}; shift
args=$(printf '%q ' "$@")
tar --exclude=.git --exclude=tests/vendor --exclude=.superpowers -czf /tmp/shipink-ext-src.tgz -C "$ROOT" .
scp -q -i "$KEY" /tmp/shipink-ext-src.tgz "$HOST":/tmp/shipink-ext-src.tgz
ssh -i "$KEY" "$HOST" "set -e
D=\$HOME/shipink-ext-dev
mkdir -p \$D/tests
find \$D -mindepth 1 -maxdepth 1 ! -name tests -exec rm -rf {} +
find \$D/tests -mindepth 1 -maxdepth 1 ! -name vendor ! -name composer.lock -exec rm -rf {} +
tar xzf /tmp/shipink-ext-src.tgz -C \$D
cd \$D/tests && composer install -q --no-interaction --no-progress
cd \$D
case '$tool' in
  phpunit) tests/vendor/bin/phpunit -c tests/phpunit.xml $args ;;
  phpcs) tests/vendor/bin/phpcs --standard=Magento2 --extensions=php,phtml,xml --ignore=tests/vendor,Test $args . ;;
  *) echo 'unknown tool: $tool' >&2; exit 2 ;;
esac"
