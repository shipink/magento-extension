# Shipink for Magento 2

Connect your Magento 2.4.6+ store to Shipink: orders sync automatically, labels with discounted carrier rates, tracking written back as Magento shipments.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6, 2.4.7 or 2.4.8
- PHP 8.1 to 8.4

## Install

With Composer:

```
composer require shipink/module-shipink
bin/magento module:enable Shipink_Shipink
bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento cache:flush
```

## Install from zip

Unzip the release into `app/code/Shipink/Shipink`, then run:

```
bin/magento module:enable Shipink_Shipink
bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento cache:flush
```

## Connect

Admin → Sales → Shipink → Connect to Shipink, sign in to Shipink, then confirm on the store's confirmation page.

Be logged in to the Magento admin when you start. If you land on the Magento dashboard instead of the confirmation page (the admin session expired), go back to Sales → Shipink and click Connect again.

## What it does

- Orders sync to Shipink. The module notifies Shipink when an order is placed, updated or canceled; Shipink also polls.
- Tracking numbers come back as Magento shipments with a "Shipink" carrier. The "Track" link opens the Shipink tracking page.
- The order page has an "Open in Shipink" button.
- Reconnecting replaces the API token Shipink uses.

## Uninstall

Composer installs:

```
bin/magento module:uninstall --remove-data Shipink_Shipink
```

Zip / `app/code` installs: add `--non-composer`.

Disabling the module does not remove its settings.

## For maintainers

Do not change the API resources in `etc/integration.xml` in a release without telling merchants to reconnect: `setup:upgrade` recreates the integration when its resources change, which invalidates the token Shipink holds. Dev checks: `bin/php-tools.sh phpunit`, `bin/php-tools.sh phpcs`, `bin/deploy-test.sh`.
