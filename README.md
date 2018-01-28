[![Build Status](https://travis-ci.org/henkelund/magento2-henhed-piwik.svg?branch=master)](https://travis-ci.org/henkelund/magento2-henhed-piwik)

Henhed_Piwik
============

*Henhed_Piwik* is a [Piwik][piwik] web analytics module for the
[Magento 2][magento] eCommerce platform.  Piwik is an extensible
free/libre analytics tool that can be self-hosted, giving you complete
data ownership.  Henhed_Piwik lets you integrate Piwik with your
Magento 2 store front.


Installation
------------

To install Henhed_Piwik, download and extract the
[master zip archive][download] and move the extracted folder to
*app/code/Henhed/Piwik* in your Magento 2 installation directory.

```sh
unzip magento2-henhed-piwik-master.zip
mkdir app/code/Henhed
mv magento2-henhed-piwik-master app/code/Henhed/Piwik
```

Alternatively, you can clone the Henhed_Piwik Git repository into
*app/code/Henhed/Piwik*.

```sh
git clone https://github.com/henkelund/magento2-henhed-piwik.git app/code/Henhed/Piwik
```

Or, if you prefer, install it using [Composer][composer].

```sh
composer require henhed/module-piwik
```

Finally, enable the module with the Magento CLI tool.

```sh
php bin/magento module:enable Henhed_Piwik --clear-static-content
```

NOTE: If you're using a Magento version prior to 2.2 you'll need to stick to the
1.x releases of Henhed_Piwik. For manual installation, check out the
[Release archive][releases]. For installation using Composer, you can use the
*tilde* or *caret* version constraint operators (e.g. `~1.3` or `^1.3.1`).


Configuration
-------------

Once intsalled, configuration options can be found in the Magento 2
administration panel under *Stores/Configuration/Sales/Piwik API*.
To start tracking, set *Enable Tracking* to *Yes*, enter the
*Hostname* of your Piwik installation and click *Save Config*.  If you
have multiple websites in the same Piwik installation, make sure the
*Site ID* configured in Magento is correct.


Customization
-------------

If you need to send some custom information to your Piwik server, Henhed_Piwik
lets you do so using event observers.

To set custom data on each page, use the `piwik_track_page_view_before` event.
A tracker instance will be passed along with the event object to your observer's
`execute` method.

```php
public function execute(\Magento\Framework\Event\Observer $observer)
{
    $tracker = $observer->getEvent()->getTracker();
    /** @var \Henhed\Piwik\Model\Tracker $tracker */
    $tracker->setDocumentTitle('My Custom Title');
}
```

If you only want to add data under some specific circumstance, find a suitable
event and request the tracker singleton in your observer's constructor. Store
the tracker in a class member variable for later use in the `execute` method.

```php
public function __construct(\Henhed\Piwik\Model\Tracker $piwikTracker)
{
    $this->_piwikTracker = $piwikTracker;
}
```

Beware of tracking user specific information on the server side as it will most
likely cause caching problems. Instead, use Javascript to retrieve the user data
from a cookie, localStorage or some Ajax request and then push the data to Piwik
using either the Henhed_Piwik JS component ..

```js
require(['Henhed_Piwik/js/tracker'], function (trackerComponent) {
    trackerComponent.getTracker().done(function (tracker) {
        // Do something with tracker
    });
});
```

.. or the vanilla Piwik approach.

```js
var _paq = _paq || [];
_paq.push(['setDocumentTitle', 'My Custom Title']);
```

See the [Piwik Developer Docs][piwik-tracking-api] or the
[\Henhed\Piwik\Model\Tracker][henhed-piwik-tracker] source code for a list of
all methods available in the Tracking API.


Disclaimer
----------

Henhed_Piwik is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU
Affero General Public License][agpl] for more details.

[agpl]: http://www.gnu.org/licenses/agpl.html
    "GNU Affero General Public License"
[composer]: https://getcomposer.org/
    "Dependency Manager for PHP"
[download]: https://github.com/henkelund/magento2-henhed-piwik/archive/master.zip
    "magento2-henhed-piwik-master"
[henhed-piwik-tracker]: https://github.com/henkelund/magento2-henhed-piwik/blob/master/Model/Tracker.php
    "Model/Tracker.php at master"
[magento]: https://magento.com/
    "eCommerce Software & eCommerce Platform Solutions"
[piwik]: http://piwik.org/
    "Free Web Analytics Software"
[piwik-tracking-api]: http://developer.piwik.org/api-reference/tracking-javascript
    "JavaScript Tracking Client"
[releases]: https://github.com/henkelund/magento2-henhed-piwik/releases
    "Henhed_Piwik Releases"
