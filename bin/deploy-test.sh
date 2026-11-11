#!/usr/bin/env bash
# Copies the module to the Magento test store and rebuilds. Usage: bin/deploy-test.sh
set -euo pipefail
KEY=${SHIPINK_TEST_KEY:?set SHIPINK_TEST_KEY to the ssh key path}
HOST=${SHIPINK_TEST_HOST:?set SHIPINK_TEST_HOST to user@host}
tar --exclude=.git --exclude=tests/vendor -czf /tmp/shipink-magento.tgz -C "$(dirname "$0")/.." .
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
