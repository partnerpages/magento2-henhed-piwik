
Magento2-PartnerPages
============

This plugin has been cloned from [magento2-henhed-piwik](https://github.com/henkelund/magento2-henhed-piwik) 
and has been customised specifically for use with the PartnerPages system.

This plugin lets you integrate PartnerPages with your
Magento 2 store front.


Installation
------------

To install Magento2-PartnerPages, download and extract the
[master zip archive][download] and move the extracted folder to
*app/code/Partnerpages/Piwik* in your Magento 2 installation directory.

```sh
unzip magento2-partnerpages-master.zip
mkdir app/code/Partnerpages
mv magento2-henhed-piwik-master app/code/Partnerpages/Piwik
```


Finally, enable the module with the Magento CLI tool.

```sh
php bin/magento module:enable Partnerpages_Piwik --clear-static-content
```


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

If you need to send some custom information to your Piwik server, Partnerpages_Piwik
lets you do so using event observers.

To set custom data on each page, use the `piwik_track_page_view_before` event.
A tracker instance will be passed along with the event object to your observer's
`execute` method.

```php
public function execute(\Magento\Framework\Event\Observer $observer)
{
    $tracker = $observer->getEvent()->getTracker();
    /* @var $tracker \Partnerpages\Piwik\Model\Tracker */
    $tracker->setDocumentTitle('My Custom Title');
}
```

If you only want to add data under some specific circumstance, find a suitable
event and request the tracker singleton in your observer's constructor. Store
the tracker in a class member variable for later use in the `execute` method.

```php
public function __construct(\Partnerpages\Piwik\Model\Tracker $piwikTracker)
{
    $this->_piwikTracker = $piwikTracker;
}
```

Beware of tracking user specific information on the server side as it will most
likely cause caching problems. Instead, use Javascript to retrieve the user data
from a cookie, localStorage or some Ajax request and then push the data to Piwik
using either the Partnerpages_Piwik JS component ..

```js
require(['Partnerpages_Piwik/js/tracker'], function (trackerComponent) {
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
[\Partnerpages\Piwik\Model\Tracker][henhed-piwik-tracker] source code for a list of
all methods available in the Tracking API.


Disclaimer
----------

Partnerpages_Piwik is distributed in the hope that it will be useful, but
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
