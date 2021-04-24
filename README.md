# Matomo Integration for Magento 2

*Chessio_Matomo* is a [Matomo](https://matomo.org/) web analytics module for the [Magento 2](https://magento.com/) eCommerce platform. Matomo is an extensible free/libre analytics tool that can be self-hosted, giving you complete data ownership. Chessio_Matomo lets you integrate Matomo with your Magento 2 store front.

This module is the successor of [*Henhed_Piwik*](https://packagist.org/packages/henhed/module-piwik) and thus continues with its semantic versioning, beginning with version `v2.1.0` . If you're using a Magento version prior to 2.2, you'll need to stick to the 1.x releases of the original Henhed_Piwik. For manual installation, check out the [Releases archive](https://github.com/fnogatz/magento2-matomo/releases). For installation using [Composer](https://getcomposer.org/), you can use the *tilde* or *caret* version constraint operators (e.g. `~1.3` or `^1.3.1`).

## Installation

To install Chessio_Matomo, download and extract the [main zip archive](https://github.com/fnogatz/magento2-matomo/archive/main.zip) and move the extracted folder to *app/code/Chessio/Matomo* in your Magento 2 installation directory.

```sh
unzip magento2-matomo-main.zip
mkdir app/code/Chessio
mv magento2-matomo-main app/code/Chessio/Matomo
```

Alternatively, you can clone the Chessio_Matomo Git repository into *app/code/Chessio_Matomo*.

```sh
git clone https://github.com/fnogatz/magento2-matomo.git app/code/Chessio/Matomo
```

Or, if you prefer, install it using [Composer](https://getcomposer.org/).

```sh
composer require chessio/module-matomo
```

Finally, enable the module with the Magento CLI tool.

```sh
php bin/magento module:enable Chessio_Matomo --clear-static-content
```

## Configuration

Once installed, configuration options can be found in the Magento 2 administration panel under *Stores/Configuration/Sales/Matomo API*.
To start tracking, set *Enable Tracking* to *Yes*, enter the *Hostname* of your Matomo installation and click *Save Config*. If you have multiple websites in the same Matomo installation, make sure the *Site ID* configured in Magento is correct.

### Using Matomo Tag Manager

You can use the Matomo Tag Manager instead of Matomo directly. Set the configuration *Enable Matomo Tag Manager Container* to yes and set the *Container Script Path*.
For details on how to configure the Matomo Tag Manager, to track ecommerce events, see doc/tag-manager.md

## Customization

If you need to send some custom information to your Matomo server, Chessio_Matomo lets you do so using event observers.

To set custom data on each page, use the `matomo_track_page_view_before` event. A tracker instance will be passed along with the event object to your observer's `execute` method.

```php
public function execute(\Magento\Framework\Event\Observer $observer)
{
    $tracker = $observer->getEvent()->getTracker();
    /** @var \Chessio\Matomo\Model\Tracker $tracker */
    $tracker->setDocumentTitle('My Custom Title');
}
```

If you only want to add data under some specific circumstance, find a suitable event and request the tracker singleton in your observer's constructor. Store the tracker in a class member variable for later use in the `execute` method.

```php
public function __construct(\Chessio\Matomo\Model\Tracker $matomoTracker)
{
    $this->_matomoTracker = $matomoTracker;
}
```

Beware of tracking user specific information on the server side as it will most likely cause caching problems. Instead, use Javascript to retrieve the user data from a cookie, localStorage or some Ajax request and then push the data to Matomo using either the Chessio_Matomo JS component...

```js
require(['Chessio_Matomo/js/tracker'], function (trackerComponent) {
    trackerComponent.getTracker().done(function (tracker) {
        // Do something with tracker
    });
});
```

... or the vanilla Matomo approach:

```js
var _paq = _paq || [];
_paq.push(['setDocumentTitle', 'My Custom Title']);
```

See the [Matomo Developer Docs](https://developer.matomo.org/api-reference/tracking-javascript) or the [\Chessio\Matomo\Model\Tracker](https://github.com/fnogatz/magento2-matomo/blob/main/Model/Tracker.php) source code for a list of all methods available in the Tracking API.
