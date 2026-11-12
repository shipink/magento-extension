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

Be logged in to the Magento admin when you start, and finish within 30 minutes. The confirmation page only accepts a connect that was started from Sales → Shipink in the same admin session; otherwise go back there and click Connect again.

## Permissions

The module creates a Magento integration named "Shipink". Its API access is limited to what Shipink uses:

- Sales → Orders → View: read orders (`GET /V1/orders`, `GET /V1/orders/{id}`)
- Sales → Orders → Ship: create shipments with tracking (`POST /V1/order/{id}/ship`)
- Sales → Shipments: list shipments (`GET /V1/shipments`)
- Stores → Settings → All Stores: read store settings, used as the connection check (`GET /V1/store/storeConfigs`)

## What it does

- Orders sync to Shipink. The module notifies Shipink when an order is placed, updated or canceled; Shipink also polls.
- Tracking numbers come back as Magento shipments with a "Shipink" carrier. The "Track" link opens the Shipink tracking page.
- The order page has an "Open in Shipink" button.
- Editing an order's address in the admin notifies Shipink.
- Reconnecting replaces the API token Shipink uses.

## Data and privacy

The module only talks to Shipink (`*.shipink.io`) and only after an admin connects the store.

- When you connect, it sends Shipink the integration's OAuth credentials, the store URL and the Magento version.
- When an order is placed, updated or canceled, it sends Shipink the order's ID and number. Nothing else is in that request.
- Shipink then reads the order through the Magento API with the permissions listed above: customer name, email, phone, shipping and billing addresses, items, totals and payment method. It uses them to create shipments and labels.
- Nothing is sent while the store is not connected.

Shipink's privacy policy: https://shipink.io/privacy/

## Support

info@shipink.io

## Uninstall

Composer installs:

```
bin/magento module:uninstall --remove-data Shipink_Shipink
```

Zip / `app/code` installs: add `--non-composer`.

Uninstalling removes the module's settings and revokes Shipink's API access (it deletes the "Shipink" integration and its OAuth tokens).

Disabling the module (`module:disable`) does NOT revoke access: the integration and its token stay active and Shipink can still read orders and create shipments. Magento does not let you delete an extension's own integration under System → Extensions → Integrations, so uninstalling (above) is the way to revoke access; you can also remove the store from your Shipink sales channels so Shipink stops using it.

Note: each time Sales → Shipink is opened it issues a new one-time connect link, so finish a connection in the same tab you started it from.

## For maintainers

The Shipink app host defaults to `app.shipink.io`. For Shipink's own testing only, point a store at another Shipink environment with:

```
bin/magento config:set shipink/general/app_host app.dev.shipink.io
```

There is no admin field for it. The connect flow only accepts callback and return URLs on this host.

Do not change the API resources in `etc/integration.xml` in a release without telling merchants to reconnect: `setup:upgrade` recreates the integration when its resources change, which invalidates the token Shipink holds. Dev checks: `bin/php-tools.sh phpunit`, `bin/php-tools.sh phpcs`, `bin/deploy-test.sh`. They run on a test server and need `SHIPINK_TEST_HOST` (`user@host`) and `SHIPINK_TEST_KEY` (path to the SSH key) in the environment.
