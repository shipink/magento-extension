#!/usr/bin/env bash
# Copies the module to the Magento test store and rebuilds.
# Usage: SHIPINK_TEST_HOST=user@host SHIPINK_TEST_KEY=/path/to/ssh.key bin/deploy-test.sh
set -euo pipefail
if [ -z "${SHIPINK_TEST_HOST:-}" ] || [ -z "${SHIPINK_TEST_KEY:-}" ]; then
  echo 'usage: SHIPINK_TEST_HOST=user@host SHIPINK_TEST_KEY=/path/to/ssh.key bin/deploy-test.sh' >&2
  exit 2
fi
KEY=$SHIPINK_TEST_KEY
HOST=$SHIPINK_TEST_HOST
tar --exclude=.git --exclude=tests/vendor --exclude=.superpowers -czf /tmp/shipink-magento.tgz -C "$(dirname "$0")/.." .
scp -q -i "$KEY" /tmp/shipink-magento.tgz "$HOST":/tmp/
ssh -i "$KEY" "$HOST" 'set -e
D=/var/www/magento/app/code/Shipink/Shipink
sudo rm -rf $D && sudo mkdir -p $D && sudo tar xzf /tmp/shipink-magento.tgz -C $D && sudo chown -R www-data:www-data /var/www/magento/app/code/Shipink
cd /var/www/magento
sudo -u www-data php bin/magento module:enable Shipink_Shipink >/dev/null
sudo -u www-data php bin/magento setup:upgrade --keep-generated >/dev/null
sudo -u www-data php bin/magento setup:di:compile >/dev/null
sudo -u www-data php bin/magento setup:static-content:deploy -f en_US >/dev/null
sudo -u www-data php bin/magento cache:flush >/dev/null
echo deployed'
